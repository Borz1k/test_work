<?
namespace Templates;

Class Tpl{
  public  $path         = '';          /* Путь к шаблонам */
  public  $plugins      = '';          /* Путь к модификаторам */
  public  $ext          = '.tpl.php';  /* Расширения шаблонов */  
  public  $all_header   = null;        /* Общий файл Header */
  public  $all_footer   = null;        /* Общий файл Footer */
  public  $header       = null;        /* Header */
  public  $footer       = null;        /* Footer */
  private $myTplerror   = [];     /* Ошибки копиляции */
  public  $tpl404       = "404";       /* Шаблон 404 ошибки */
  public  $PAGE         = [];
  private $Debug        = false;       /* Выводить консоль компиляции шаблона */
  private $_data        = [];     /* Переменные входищие в шаблон */
  private $load         = 0;           /* Время генерации шаблона */
  public  $_data_log    = [];
  private $DebugConsole = ['load'=>false,'templates'=>[]]; 
  private $_templates   = [];          
  
  public function display($tpl,$full = true){

    $load_tpl = $this->path.$tpl.$this->ext;
    $load_tpl = is_file($load_tpl) ? $load_tpl : $tpl.$this->ext;
    if(is_file($load_tpl)){ 
        
      $this->_templates[md5($load_tpl)] = array('tpl'=>$load_tpl,'time'=>0);
      if($full){
        
        if($this->all_header && is_file($this->path.$this->all_header.$this->ext)){
          include($this->path.$this->all_header.$this->ext);
        }
        
        if($this->header && is_file($this->path.$this->header.$this->ext)){
          include($this->path.$this->header.$this->ext);
        }else{
          if($this->header) $this->myTplerror[] = "Нет шаблона ".$this->path.$this->header.$this->ext;
        }
      }
      include($load_tpl);
      
      if($full){
        if($this->footer && is_file($this->path.$this->footer.$this->ext)){
          include($this->path.$this->footer.$this->ext);
        }else{
          if($this->footer) $this->myTplerror[] = "Нет шаблона ".$this->path.$this->footer.$this->ext;
        }
        if($this->all_footer && is_file($this->path.$this->all_footer.$this->ext)){
          include($this->path.$this->all_footer.$this->ext);
        }
      }     

      if($this->Debug){
        if($this->DebugConsole['load']===false){
          $this->DebugConsole['load'] = true;
          $this->DebugConsole['templates'][] = $load_tpl;
          $this->displayDebug();
        }else{
          $this->DebugConsole['templates'][] = $load_tpl;
        }
      }
    }else{
      die("Нет шаблона ".$load_tpl);
    }
  }
   public function _include($tpl,$data) {
	$load_tpl = $this->path.$tpl.$this->ext;
    $load_tpl = is_file($load_tpl) ? $load_tpl : $tpl.$this->ext;
    include($load_tpl);
  }
  function setTitle($set=''){
    $this->PAGE['meta_title'] = $set;
  }
  function setKeywords($set=''){
    $this->PAGE['meta_keywords'] = $set;
  }
  function setDescription($set=''){
    $this->PAGE['meta_description'] = $set;
  }
  function setContent($set=''){
    $this->PAGE['content'] = $set;
  }
  function setContentTop($set=''){
    $this->PAGE['content_top'] = $set;
  }
  function setContentBottom($set=''){
    $this->PAGE['content_bottom'] = $set;
  }
  
  function error404(){
    header("HTTP/1.0 404 Not Found");
    $this->PAGE = array('meta_title'=>'404 ERROR Нет такой страницы','meta_name'=>'404 ERROR Нет такой страницы');
		$this->header     = "main/header";
    $this->footer     = "main/footer";
    $this->display($this->tpl404);
    die();
  }
  
  public function fetch($tpl,$full=false) {
      ob_start();
      $this->display($tpl,$full);
      $content = ob_get_contents();
      ob_clean();
      return $content;
  }
   
  public function __set($key, $value) {
    $this->_data[$key] = $value;
  }
   
  public function &__get($key) {
    return $this->_data[$key];
  }
}
?>