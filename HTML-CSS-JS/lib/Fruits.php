<?
Class Fruits{
	private $fruit_data = false;
	private $db 		= false;

	public function __construct($db){
		$this->db = $db;
	}

	public function getFruit(){
		return $fruit_data; 
	}

	public function setFruit($fruitId = 0){
		$this->fruit_data = $this->db->SelectRecord("fruits", "", "id=".$this->db->Quote($fruitId)); 
		return ($this->db->error ? false : true);
	}

	public function getAllFruit(){
		return $this->db->Select("fruits","","","ORDER BY id");
	}

	public function addFruit($data = []){
		$id = $this->db->Insert("fruits", $data);
		return ($this->db->error ? false : $id);
	}
}