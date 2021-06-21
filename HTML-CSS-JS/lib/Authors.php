<?
Class Authors{
	protected $author_data = false;
	protected $count_books = 0;
	protected $db 		   = false;

	public function __construct($db){
		$this->db = $db;
	}

	public function getAuthor(){
		return $author_data; 
	}

	public function setAuthor($authorId = 0){
		$this->count_books = 0;
		$this->author_data = $this->db->SelectRecord("authors", "", "id=".$this->db->Quote($authorId)); 
		return $this->db->error ? false : true;
	}
}