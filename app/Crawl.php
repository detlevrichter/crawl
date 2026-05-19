<?php

use function Laravel\Prompts\table;
use AIConnection\Utils\Show;
use League\HTMLToMarkdown\HtmlConverter;

class Crawl extends Model
{
    private string $model;

    public const PAGE_TYPE = 'page_type';
    public const CRAWL_LIST_TABLE = 'crawl_list';
    public const CRAWL_MASTER_TABLE = 'crawl_master';

    public const PAGE_TYPE_EVENT_DETAIL = 'event-detail';
    public const PAGE_TYPE_EVENT_LIST = 'event-list';
    public const PAGE_TYPE_OTHER = 'other';

    public const EVENT = 'event';

    public const LANUGAGE = 'language';

    public const ERROR_LLM_NOT_ACTIVE = 'LLM_NOT_ACTIVE';
    public const ERROR_NO_API_KEY = 'NO_API_KEY';
    public const ERROR_API_ERROR = 'API_ERROR';
    public const ERROR_JSON_PARSE_FAILED = 'JSON_PARSE_FAILED';
    public const ERROR_EMPTY_RESPONSE = 'EMPTY_RESPONSE';

    public $masterCrawlData;

    private $content = '';
    private $promptTokens = 0;
    private $completionTokens = 0;
    private $stopReason = '';


    /**
     * @param string $model
     */
    public function __construct(string $model = 'meta-llama-3.1-70b-instruct')
    {
        $pdo = DB::DB()->PDO();
        self::setConnection($pdo);
        $this->model = $model;
    }

    public function setCrawlMasterData($masterCrawlData){
        $this->masterCrawlData = $masterCrawlData;
    }

    public function savePossibleDetailPage($url){
        $enty = new self();
        $data = [
            'master_id' => $this->masterCrawlData->id,
            'url' => $url,
        ];
        $enty->fill($data);
        $enty->table = self::CRAWL_LIST_TABLE;
        $enty->save();

    }


    protected function performInsert(): bool
    {
        $this->attributes['created_at'] = date('Y-m-d H:i:s');
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        // Arbeitskopie
        $data = $this->attributes;

        // Primärschlüssel beim Insert raus, wenn leer
        if (array_key_exists($this->primaryKey, $data) && empty($data[$this->primaryKey])) {
            unset($data[$this->primaryKey]);
        }

        if (empty($data)) {
            throw new \Exception("Keine Daten zum Einfügen vorhanden.");
        }

        // Spalten/Parameter aufbauen + Werte normalisieren
        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $col => $val) {
            // Sicherheitshalber noch einmal PK filtern, falls null
            if ($col === $this->primaryKey && ($val === null || $val === '')) {
                continue;
            }

            $columns[] = $col;
            $placeholders[] = ':' . $col;
            $params[$col] = $this->normalizeForDatabase($val);
        }

        if (empty($columns)) {
            throw new \Exception("Keine gültigen Spalten zum Einfügen.");
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)  ",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        ). " ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP";
 
        $stmt = self::$connection->prepare($sql);
        $result = $stmt->execute($params);
 
        // PK nachziehen
        if ($result && !isset($this->attributes[$this->primaryKey])) {
            $this->attributes[$this->primaryKey] = self::$connection->lastInsertId();
        }

        return $result;
    }

    public function setBadPage($url){
        $clt = self::CRAWL_LIST_TABLE;
        /*
        vielleicht direkt löschen?
        $sql = "DELETE FROM $clt WHERE url LIKE :url";
        DB::DB()->query($sql,['url'=>$url]);*/
        /* vielleicht nicht löschen sondern nur falsch setzen?     */
        $sql = "UPDATE {self::CRAWL_LIST_TABLE} SET Status = :status WHERE url LIKE :url";
        $this->table = self::CRAWL_LIST_TABLE;
        $dbresult = $this->getByAttribute(['url' => $url], PDO::FETCH_ASSOC);
                $enty = new self();
        $data = $dbresult[0];
        $data['Status'] = 'FALSE';
        $enty->fill($data);

        $enty->table = self::CRAWL_LIST_TABLE;
        $enty->save();
   

    }
/**
 * Diese Funtkion kann eigentlich immer ausgeführt werden, denn sie soll nur verwaiste Einträge löschen.
 */
    public function referentialIntegrity(){
        $masterCrawlTable = self::CRAWL_MASTER_TABLE;
        $crawlListTable = self::CRAWL_LIST_TABLE;
        $offerTable = Offer::OFFER_TABLE;
        $offerCompetencies = 'offer_competencies';
        // nichts in der crawl list wo es keinen master gibt
        DB::DB()->query("DELETE $crawlListTable FROM $crawlListTable LEFT JOIN $masterCrawlTable ON $masterCrawlTable.id = master_id WHERE $masterCrawlTable.id  is null" );
        // keine offers die nicht in der crawl liste sind
        DB::DB()->query("DELETE $offerTable FROM $offerTable LEFT JOIN $crawlListTable ON $crawlListTable.id = crawl_list_id WHERE $crawlListTable.id  is null" );
        // keine offer-Eigenschaften (offer_competencies) wenn es keine offers gibt
        DB::DB()->query("DELETE $offerCompetencies FROM $offerCompetencies LEFT JOIN $offerTable ON $offerTable.id = offer_id WHERE $offerTable.id  is null" );

    }
/**
 * precrawl füllt die crawl_list
 */
    public function preCrawl(){
        $gesamt = 0;
        $masterCrawlTable = self::CRAWL_MASTER_TABLE;
        $crawlListTable = self::CRAWL_LIST_TABLE;
        $masterCrawlURLs = DB::DB()->query("SELECT * FROM $masterCrawlTable WHERE Detailseite LIKE :Detailseite OR Detailseite is null",['Detailseite'=>'Nein']);

        foreach($masterCrawlURLs as $masterCrawlURL ){
            $parsedURL = parse_url($masterCrawlURL->URL);

            $command = NODEJS_EXE . " " . dirname(__DIR__) . "/pup.js ". $masterCrawlURL->URL;
            exec($command, $output, $return_var);
            // entfernen leerer Elemente
            $output = array_filter($output);
            $source =  join("\r\n", $output);

            preg_match_all('~href="([^#][^"]*)"~m', $source, $detailUrls);
            
            $this->setCrawlMasterData($masterCrawlURL);
            foreach($detailUrls[1] as $detailUrl){
                $parsedDetailURL = parse_url($detailUrl); 
                if(!isset($parsedDetailURL['host'])){
                $detailUrl = $parsedURL['scheme'].'://'.$parsedURL['host']. $detailUrl;
                }
                if(strstr($detailUrl, $masterCrawlURL->Verzeichnis)!==false  && strcmp($detailUrl, $masterCrawlURL->URL ) != 0 ){
                $this->savePossibleDetailPage($detailUrl);
                $gesamt++;

                //echo $detailUrl;
                //echo '<br>';
                }

            }
        }
        return $gesamt; 
    }

    public function crawl()
    {

        $crawlListTable = 'crawl_list';
        $model = 'meta-llama-3.1-70b-instruct';
        $c = $this;
        // var_dump($c);
        $client = new Show();
        $converter = new HtmlConverter();
        $converter->getConfig()->setOption('strip_tags', true);
        $eventInfo = '';
        $badPagesCount = 0;
        $master = [];
        $competencies = DB::DB()->query("SELECT * from competency_types ORDER BY id ASC");

        $crawlListURLs = DB::DB()->query("SELECT * FROM $crawlListTable ");
        $gesamtSeiten = count((array)$crawlListURLs); 
        $pobject = new Prompt(); 
        $prompt = $pobject->get();
        // verbose( '<pre>');
        // verbose(var_export($crawlListURLs, true));
        $i = 0;
        foreach ($crawlListURLs as $crawlListURL) {
            usleep(MICRO_SLEEP_TIME);
            $i++;
            $this->setProgress(round($i/$gesamtSeiten,3)*100, 'KI fragen - Seite '. $i .' von ' . $gesamtSeiten . ' davon schlechte Seiten: '. $badPagesCount);
            echo ('<strong>Crawle ' . ' Detailseite</strong> <small>' . $crawlListURL->url . '</small>');
            echo ('Hole Quelltext');
            $command = NODEJS_EXE . " " . dirname(__DIR__) . "/pup.js " . $crawlListURL->url;
            exec($command, $output, $return_var);
            // entfernen leerer Elemente
            $output = array_filter($output);
            $source =  join("\r\n", $output);
            $source = Show::cleanHtml($source);
            $markdown = Show::cleanMarkup($converter->convert($source));
            
            $master = DB::DB()->query("SELECT * from crawl_master WHERE id =".(int)$crawlListURL->master_id );

            echo ('Frage bei der KI nach');
 
            $answers = $client->chat($markdown, $model, $prompt);
            $offer = null;
            foreach ($answers as $answer) {
                echo ('<br><strong>Ergebnis</strong><hr>' . nl2br($answer) . '<hr>');
                $answer = trim($answer, "json \n\r\t\v\0`");
                if (strcmp($answer, 'FALSE') == 0 || strstr($answer,'Keine Antwort von der AI') !== false) {
                    // Sperre diese Seite
                    $c->setBadPage($crawlListURL->url);
                    $this->setProgress(round($i/$gesamtSeiten,3)*100, 'KI findet nix bei - Seite '. $i .' von ' . $gesamtSeiten .' url: '. $crawlListURL->url );
                     $badPagesCount++;
                    continue;
                }
                try {
                    $eventInfo = json_decode($answer, true);
                } catch (\JsonException $jsonException) {
                    throw new JsonException("Etwas ist schiefgelaufen mit JSON" . $jsonException);
                }
                $offer = new Offer([
                    Offer::OFFER_CRAWL_LIST_ID => $crawlListURL->id,   //$webtext->id,
                    Offer::OFFER_URL => $crawlListURL->url, // $webtext->{Webtexts::WEBTEXTS_URL},
                ]);
                if (is_null($eventInfo)) {
                    continue;
                }
                $eventInfo['provider'] = $master[0]->Name;
                try {
                    //code...
                    $offer->updateFromLLM($eventInfo);
                } catch (\Throwable $th) {
                    //throw $th;
                    trigger_error(print_r($eventInfo,1));
                    //throw new Exception(print_r($eventInfo,1), 1);
                    $this->logThrowable($th);
                    continue;
                }
                if (!$offer->id) continue;
                $eventInfo['offer_id'] = $offer->id;

                $offercompetency = new OfferCompetency($eventInfo);
                $offercompetency->purge()->save();


            }
        }
    }



    /**
     * 
     */
    public function runCrawl($progressFile){
        $this->progressFile = $progressFile;
        $this->setProgress(1, 'Detailliste holen');
        $this->preCrawl();
        $this->setProgress(9, 'Detailliste geholt');
        $this->referentialIntegrity();
        $this->setProgress(10, 'referenzielle Integrität hergestellt');
        $this->crawl();
    }

    /**
     * 
     */
    public function setProgress($i, $job = 'working'){
        file_put_contents($this->progressFile, json_encode([
                'time' => date('Y-m-d H:i:s'),
                'job' => $job,
                'progress' => round($i,3),
                'status' => $i < 100 ? 'running' : 'done',
                'pid' => getmypid()
            ]));
    }

}