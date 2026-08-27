<?php

use function Laravel\Prompts\table;
use AIConnection\Utils\Show;
use League\HTMLToMarkdown\HtmlConverter;

class Prompt extends Model
{
    protected $masterID;

    public function get($masterID = 0){
        $this->masterID = $masterID;
        $masterFields = $this->getMasterPart('FieldsPrompt');
        $masterFieldsExample = $this->getMasterPart('FieldsExample');
        $masterPrePrompt = $this->getMasterPart('PrePrompt');
        $masterPostPrompt = $this->getMasterPart('PostPrompt');
        $masterFields = trim($masterFields);
        $masterFieldsExample = trim($masterFieldsExample);
        $masterFieldsExampleArray = [];//preg_split()
        $lines = explode("\n", $masterFieldsExample);
        foreach ($lines as $line) {
            if (trim($line) === '') continue; // leere Zeilen überspringen

            list($key, $value) = explode(":", $line, 2);
            $masterFieldsExampleArray[$key] = trim( $value);
        }
       
        $competencies = DB::DB()->query("SELECT * from competency_types ORDER BY id ASC");

        $examplesArray = [
            Offer::OFFER_TITLE => 'Einführung in die Python Programmierung',
            Offer::OFFER_DESCRIPTION => 'In diesem Kurs lernen Sie die Grundlagen der Python Programmierung.',
            Offer::OFFER_LEVEL => "0.7"

        ];
        $bspval = [0, 0, 1, 1, 0.5, 0.25, 0.75];
        $i = 0;
        foreach ($competencies as $competence) {
            $examplesArray[$competence->slug] =  $bspval[mt_rand(0, count($bspval) - 1)];
        }
        $examplesArray = array_merge($examplesArray,$masterFieldsExampleArray);
         $basicEventInfoMessage = $this->getPart('preprompt'). "\n\n" .
                '<fields>' . "\n" .
                "title: ".$this->getPart('title')."\n" .
                "description: ".$this->getPart('description')."\n" .
                'level: '.$this->getPart('level') . "\n" ;
                foreach ($competencies as $competence) {
                    $basicEventInfoMessage .=  $competence->slug . ': ' .  str_replace(["\r", "\n"], '', $competence->description) . "\n";
                    $examplesArray[$competence->slug] =  str_replace(["\r", "\n"], '', trim($competence->example));
                } 
                if($masterFields){
                    $basicEventInfoMessage .=  $masterFields;
                }

            $basicEventInfoMessage .=             '</fields>' . "\n\n" .
                //   'ES IST SEHR WICHTIG DAS DIE ANTWORT IN JSON FORMATIERT IST UND DIE FELDER WIE OBEN BESCHRIEBEN ENTHÄLT.' . "\n\n" .
                 $this->getPart('postprompt')  . "\n\n" .
        
                '<example>' . "\n" .
                json_encode($examplesArray, JSON_PRETTY_PRINT) . "\n";
                
            $basicEventInfoMessage .=     '</example>';

        return $masterPrePrompt . "\n" . $basicEventInfoMessage . "\n" . $masterPostPrompt;
    }
    public function getPart($identifier, $part = 'field'){
        $data = DB::DB()->single("SELECT data FROM prompt WHERE identifier = :identifier",['identifier'=>$identifier]);
        return $data;
    }
    public function getMasterPart($identifier = 'FieldsPrompt'){
        $data = DB::DB()->single("SELECT $identifier FROM crawl_master WHERE id = :id",['id'=>$this->masterID]);
        return $data;
    }

}