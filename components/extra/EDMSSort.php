<?php
/**
 * Class and Function List:
 * Function list:
 * - applyOrder()
 * - setRequestVar()
 * - getRequestVar()
 * - getDirections()
 * Classes list:
 * - EDMSSort extends CSort
 */

class EDMSSort extends CSort {
	const SORT_ASC = 1;
    const SORT_DESC = -1;   
    /**
     * Sort descending
     * @since 1.1.10
     */

    
    public $requestVar = '_GET';
    public $baseModelClass = 'EMongoDocument';
    private $_directions;
    private $_reqParams;

    /**
     * Modifies the query criteria by changing its {@link EMongoCriteria::sort} property.
     * This method will use {@link directions} to determine which columns need to be sorted.
     * They will be put in the sort array. If the criteria already has non-empty {@link EMongoCriteria::sort} value,
     * the new value will be appended to the array.
     * @param EMongoCriteria $criteria the query criteria
     */
    public function applyOrder($criteria) {
        $order = $this->getOrderBy($criteria);
        if (!empty($order)){
        	$sort = $criteria->getSort();
        	foreach($order as $attr=>$dir){
        		$sort[$attr] = $dir;
        	}
        	$criteria->setSort($sort);
        }
    }

    /**
     * Modified from cSort to use EDMSSort::getRequestVar instead of assuming $_GET.
     *
     * @return array The directions array
     *
     */
    public function getDirections() {
        if ($this->_directions === null) {
            $this->_directions = array();
            if (($sortVar = $this->getRequestVar($this->sortVar)) !== false) {
                $attributes = explode($this->separators[0], $sortVar);
                
                foreach ($attributes as $attribute) {
                    if (($pos = strrpos($attribute, $this->separators[1])) !== false) {
                        $descending = substr($attribute, $pos + 1) === $this->descTag;
                        if ($descending) $attribute = substr($attribute, 0, $pos);
                    } else $descending = false;
                    if (($this->resolveAttribute($attribute)) !== false) {
                        $this->_directions[$attribute] = $descending;
                        if (!$this->multiSort) 
                        return $this->_directions;
                    }
                }
            }
            if ($this->_directions === array() && is_array($this->defaultOrder)) $this->_directions = $this->defaultOrder;
        }
        
        return $this->_directions;
    }


    /**
	 * 
	 * Returns the real definition of an attribute given its name.
	 * 
	 * The resolution is based on {@link attributes} and {@link EMongoDocument::attributeNames}.
	 * <ul>
	 * <li>When {@link attributes} is an empty array, if the name refers to an attribute of {@link modelClass},
	 * then the name is returned back.</li>
	 * <li>When {@link attributes} is not empty, if the name refers to an attribute declared in {@link attributes},
	 * then the corresponding virtual attribute definition is returned. Starting from version 1.1.3, if {@link attributes}
	 * contains a star ('*') element, the name will also be used to match against all model attributes.</li>
	 * <li>In all other cases, false is returned, meaning the name does not refer to a valid attribute.</li>
	 * </ul>
	 * @changed Modified to use EDMSSort::baseModelClass instead of assuming CActiveRecord.
	 * @param string $attribute the attribute name that the user requests to sort on
	 * @return mixed the attribute name or the virtual attribute definition. False if the attribute cannot be sorted.
	 */
	public function resolveAttribute($attribute)
	{	
		$klass = $this->baseModelClass;
		if($this->attributes!==array())
			$attributes=$this->attributes;
		else if($this->modelClass!==null)
			$attributes= $klass::model($this->modelClass)->attributeNames();
		else
			return false;
		foreach($attributes as $name=>$definition)
		{
			if(is_string($name))
			{
				if($name===$attribute)
					return $definition;
			}
			else if($definition==='*')
			{
				if($this->modelClass!==null && $klass::model($this->modelClass)->hasAttribute($attribute))
					return $attribute;
			}
			else if($definition===$attribute)
				return $attribute;
		}
		return false;
	}


	/**
	 * Creates a URL that can lead to generating sorted data.
	 * @param CController $controller the controller that will be used to create the URL.
	 * @param array $directions the sort directions indexed by attribute names.
	 * The sort direction can be either CSort::SORT_ASC for ascending order or
	 * CSort::SORT_DESC for descending order.
	 * @return string the URL for sorting
	 */
	public function createUrl($controller,$directions)
	{
		$sorts=array();
		foreach($directions as $attribute=>$descending)
			$sorts[]=$descending ? $attribute.$this->separators[1].$this->descTag : $attribute;
		$params=$this->params===null ? $this->getRequestVar() : $this->params;
		$params[$this->sortVar]=implode($this->separators[0],$sorts);
		return $controller->createUrl($this->route,$params);
	}


	/**
	 * Resolves the attribute label for the specified attribute.
	 * This will invoke {@link EMongoDocument::getAttributeLabel} to determine what label to use.
	 * If the attribute refers to a virtual attribute declared in {@link attributes},
	 * then the label given in the {@link attributes} will be returned instead.
	 * @param string $attribute the attribute name.
	 * @return string the attribute label
	 */
	public function resolveLabel($attribute)
	{	
		$definition=$this->resolveAttribute($attribute);
		if(is_array($definition))
		{
			if(isset($definition['label']))
				return $definition['label'];
		}
		else if(is_string($definition))
			$attribute=$definition;
		if($this->modelClass!==null){
			$klass = $this->baseModelClass;
			return $klass::model($this->modelClass)->getAttributeLabel($attribute);
		}
		else
			return $attribute;
	}



	/**
	 * @param CDbCriteria $criteria the query criteria
	 * @return string the order-by columns represented by this sort object.
	 * This can be put in the ORDER BY clause of a SQL statement.
	 * @since 1.1.0
	 */
	public function getOrderBy($criteria=null)
	{
		$directions=$this->getDirections();
		if(empty($directions))
			return is_array($this->defaultOrder) ? $this->defaultOrder : array();
		else
		{
			
			$orders=array();
			foreach($directions as $attribute=>$descending)
			{
				$definition=$this->resolveAttribute($attribute);
				if(is_array($definition))
				{
					if($descending)
						$orders[$attribute]= static::SORT_DESC;
					else
						$orders[$attribute]= static::SORT_ASC;
				}
				else if($definition!==false)
				{
					$attribute=$definition;
					$orders[$attribute]= $descending? static::SORT_DESC : static::SORT_ASC;
				}
			}
			return $orders;
		}
	}



        /**
     * Set the Request Variable to use.
     * @param string|array|function $name Usually or 'GET','POST','REQUEST', but can also be a json_encoded string or
     * it can also accept an array, or alternatively if $name is_callable it will call it and use the result.
     *
     *
     */
    protected function setRequestVar($name = null) {
        if (empty($name)) {
            $name = $this->requestVar;
        }
        // Its the name of a var like _GET,_REQUEST,_POST
        if (is_string($name)) {
            
            switch ($name) {
                case '_GET':
                case 'GET':
                    $this->_reqParams = & $_GET;
                    break;

                case '_POST':
                case 'POST':
                    $this->_reqParams = & $_POST;
                    break;

                case '_REQUEST':
                case 'REQUEST':
                    $this->_reqParams = & $_REQUEST;
                    break;

                default:
                    if ($name[0] == '{') {
                        $this->_reqParams = json_decode($name, true);
                    } else {
                        $this->_reqParams = & $_GET;
                    }
            }
        } else if (is_array($name)) {
            $this->_reqParams = $name;
        } else if (is_callable($name)) {
            $this->_reqParams = call_user_func($name);
        }
    }
    /**
     * Get a Request Parameter or if $name is null the whole request array.
     * @param string $name The name of the request variable to get, usually $this->sortVar
     * @return string the value of the request variable or false if not found.
     *
     *
     */
    public function getRequestVar($name = null) {
        if (!isset($this->_reqParams)) {
            $this->setRequestVar();
        }
        if (is_null($name)) {
            
            return $this->_reqParams;
        } else if (isset($this->_reqParams[$name])) {
            
            return $this->_reqParams[$name];
        } else 
        return false;
    }
}
