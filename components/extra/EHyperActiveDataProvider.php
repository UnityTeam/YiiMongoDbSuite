<?php



class EHyperActiveDataProvider extends CActiveDataProvider {
    
    public $baseModelClass = 'EMongoDocument';
    public $criteriaClass = 'EMongoCriteria';
    private $_criteria;


    public function __construct($modelClass,$config=array()){

            if(is_string($modelClass)){
                $this->modelClass = $modelClass;
                $this->model = $modelClass::model();
            }
            else if(is_a($modelClass, $this->baseModelClass)){
               $this->modelClass = get_class($modelClass);
                $this->model = $modelClass;
            }
            $this->setId($this->modelClass);
            
            foreach($config as $key=>$value){
                $this->$key = $value;
                }
            
    }

    public function getCriteria(){
        if($this->_criteria===null)
            $this->setCriteria(null);
        return $this->_criteria;
    }


 
    /**
     * Sets the query criteria.
     * @param array $value the query criteria. Array representing the MongoDB query criteria.
     * @since v1.0
     */
    public function setCriteria($criteria)
    {
            $r = new ReflectionClass($this->criteriaClass);
            if (is_array($criteria)){
                $this->_criteria = $r->newInstance($criteria);
            }
            else if(is_a($criteria, $this->criteriaClass)){
                $this->_criteria = $criteria;
            }
    }


    /**
     * Fetches the data item keys from the persistent data storage.
     * @return array list of data item keys.
     * @since v1.0
     */
    protected function fetchKeys()
    {
        $keys = array();
        foreach($this->getData() as $i=>$data)
        {
            $keys[$i] = $data->{$this->keyField};
        }
        return $keys;
    }


    public function getSort(){
        if($this->_sort===null)
        {
            $this->_sort=new EDMSSort($this->modelClass);
            if(($id=$this->getId())!='')
                $this->_sort->sortVar=$id.'_sort';
        }
        return $this->_sort;

    }











}