<?

Class Books extends Authors{
	private $book_data   = false;

	public function __construct($db){
		parent::__construct($db);
	}

	public function getAuthor(){
		return $book_data; 
	}

	public function setBook($bookId = 0){
		$this->book_data = $this->db->SelectRecord("books", "", "id=".$this->db->Quote($bookId)); 
		return $this->db->error ? false : true;
	}

	public function authorBooks(){	
		if($this->count_books==0){
			$this->count_books = $this->db->SelectRecord("books", "COUNT(*) AS c", "author_id=".$this->author_data['id'])['c'];
		}
		return $this->count_books;	 	
	}
}