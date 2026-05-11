<?php
class Offer extends Model
{

    public const OFFER_TABLE = 'offers';
   // public const OFFER_WEBTEXTS_ID = Webtexts::WEBTEXTS_TABLE . '_id';
    public const OFFER_CRAWL_LIST_ID = 'crawl_list_id';
    public const OFFER_URL = 'url';
    public const OFFER_PROVIDER = 'provider';
    public const OFFER_TITLE = 'title';
    public const OFFER_DESCRIPTION = 'description';
    public const OFFER_LEVEL = 'level';
    public const OFFER_ACTIVE = 'active';

    // Relations
    public const OFFER_CATEGORIES = 'categories';
    public const OFFER_COMPETENCIES = 'competencies';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $table = self::OFFER_TABLE;
    
    public function __construct(array $attributes = [])
    {
        $pdo = DB::DB()->PDO();
        self::setConnection($pdo);
        $this->fill($attributes);
        if(isset($attributes[self::OFFER_URL])){
           $dbresult = $this->getByAttribute([self::OFFER_URL => $attributes[self::OFFER_URL]]);
           if($dbresult[0] ?? false){
               $attributes['id'] = $dbresult[0]->id;
           }
        }
        parent::__construct($attributes);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::OFFER_CRAWL_LIST_ID,
        self::OFFER_URL,
        self::OFFER_PROVIDER,
        self::OFFER_TITLE,
        self::OFFER_DESCRIPTION,
        self::OFFER_LEVEL,
        self::OFFER_ACTIVE,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        self::OFFER_CRAWL_LIST_ID => 'integer',
        self::OFFER_URL => 'string',
        self::OFFER_PROVIDER => 'string',
        self::OFFER_TITLE => 'string',
        self::OFFER_DESCRIPTION => 'string',
        self::OFFER_LEVEL => 'float',
        self::OFFER_ACTIVE => 'boolean',
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
   /*     self::UPDATED_AT,*/
        self::OFFER_URL,
        self::OFFER_PROVIDER,
        self::OFFER_TITLE,
        self::OFFER_DESCRIPTION,
        self::OFFER_LEVEL,
        self::OFFER_ACTIVE,
    ];
    /*public function fill(array $data){
        $sql = "REPLACE INTO ".self::OFFER_TABLE." ";
    }*/
    public function updateFromLLM(array $data): bool
    {

        $level = Arr::get($data, Offer::OFFER_LEVEL);
        if (is_null($level) || Levels::tryFrom((string)$level) === null) {
            $level = 0;
        }

        $this->fill([
            Offer::OFFER_PROVIDER => Arr::get($data, Offer::OFFER_PROVIDER),
            Offer::OFFER_TITLE => Arr::get($data, Offer::OFFER_TITLE),
            Offer::OFFER_DESCRIPTION => Arr::get($data, Offer::OFFER_DESCRIPTION),
            Offer::OFFER_LEVEL => (float)$level,
        ]);

        $offerSaved = $this->save();

        if (!$offerSaved) {
  //          Log::info('Offer not saved with Claude data');
            return false;
        }
/*
*/
        return true;
    }










}