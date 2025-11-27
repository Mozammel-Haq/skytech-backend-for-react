<?php
class TestOrderLog extends Model implements JsonSerializable{
	public $id;
	public $order_id;
	public $status;
	public $created_at;

	public function __construct(){
	}
	public function set($id,$order_id,$status,$created_at){
		$this->id=$id;
		$this->order_id=$order_id;
		$this->status=$status;
		$this->created_at=$created_at;

	}
	public function save(){
		global $db,$tx;
		$db->query("insert into {$tx}test_order_logs(order_id,status,created_at)values('$this->order_id','$this->status','$this->created_at')");
		return $db->insert_id;
	}
	public function update(){
		global $db,$tx;
		$db->query("update {$tx}test_order_logs set order_id='$this->order_id',status='$this->status',created_at='$this->created_at' where id='$this->id'");
	}
	public static function delete($id){
		global $db,$tx;
		$db->query("delete from {$tx}test_order_logs where order_id={$id}");
	}
	public function jsonSerialize():mixed{
		return get_object_vars($this);
	}
	public static function all(){
		global $db,$tx;
		$result=$db->query("select id,order_id,status,created_at from {$tx}test_order_logs");
		$data=[];
		while($testorderlog=$result->fetch_object()){
			$data[]=$testorderlog;
		}
			return $data;
	}
	public static function pagination($page=1,$perpage=10,$criteria=""){
		global $db,$tx;
		$top=($page-1)*$perpage;
		$result=$db->query("select id,order_id,status,created_at from {$tx}test_order_logs $criteria limit $top,$perpage");
		$data=[];
		while($testorderlog=$result->fetch_object()){
			$data[]=$testorderlog;
		}
			return $data;
	}
	public static function count($criteria=""){
		global $db,$tx;
		$result =$db->query("select count(*) from {$tx}test_order_logs $criteria");
		list($count)=$result->fetch_row();
			return $count;
	}
	public static function find($id){
		global $db,$tx;
		$result =$db->query("select id,order_id,status,created_at from {$tx}test_order_logs where id='$id'");
		$testorderlog=$result->fetch_object();
			return $testorderlog;
	}
	static function get_last_id(){
		global $db,$tx;
		$result =$db->query("select max(id) last_id from {$tx}test_order_logs");
		$testorderlog =$result->fetch_object();
		return $testorderlog->last_id;
	}
	public function json(){
		return json_encode($this);
	}
	public function __toString(){
		return "		Id:$this->id<br> 
		Order Id:$this->order_id<br> 
		Status:$this->status<br> 
		Created At:$this->created_at<br> 
";
	}

	//-------------HTML----------//

	static function html_select($name="cmbTestOrderLog"){
		global $db,$tx;
		$html="<select id='$name' name='$name'> ";
		$result =$db->query("select id,name from {$tx}test_order_logs");
		while($testorderlog=$result->fetch_object()){
			$html.="<option value ='$testorderlog->id'>$testorderlog->name</option>";
		}
		$html.="</select>";
		return $html;
	}
	static function html_table($page = 1,$perpage = 10,$criteria="",$action=true){
		global $db,$tx,$base_url;
		$count_result =$db->query("select count(*) total from {$tx}test_order_logs $criteria ");
		list($total_rows)=$count_result->fetch_row();
		$total_pages = ceil($total_rows /$perpage);
		$top = ($page - 1)*$perpage;
		$result=$db->query("select id,order_id,status,created_at from {$tx}test_order_logs $criteria limit $top,$perpage");
		$html="<table class='table'>";
			$html.="<tr><th colspan='3'>".Html::link(["class"=>"btn btn-success","route"=>"testorderlog/create","text"=>"New TestOrderLog"])."</th></tr>";
		if($action){
			$html.="<tr><th>Id</th><th>Order Id</th><th>Status</th><th>Created At</th><th>Action</th></tr>";
		}else{
			$html.="<tr><th>Id</th><th>Order Id</th><th>Status</th><th>Created At</th></tr>";
		}
		while($testorderlog=$result->fetch_object()){
			$action_buttons = "";
			if($action){
				$action_buttons = "<td><div class='btn-group' style='display:flex;'>";
				$action_buttons.= Event::button(["name"=>"show", "value"=>"Show", "class"=>"btn btn-info", "route"=>"testorderlog/show/$testorderlog->id"]);
				$action_buttons.= Event::button(["name"=>"edit", "value"=>"Edit", "class"=>"btn btn-primary", "route"=>"testorderlog/edit/$testorderlog->id"]);
				$action_buttons.= Event::button(["name"=>"delete", "value"=>"Delete", "class"=>"btn btn-danger", "route"=>"testorderlog/confirm/$testorderlog->id"]);
				$action_buttons.= "</div></td>";
			}
			$html.="<tr><td>$testorderlog->id</td><td>$testorderlog->order_id</td><td>$testorderlog->status</td><td>$testorderlog->created_at</td> $action_buttons</tr>";
		}
		$html.="</table>";
		$html.= pagination($page,$total_pages);
		return $html;
	}
	static function html_row_details($id){
		global $db,$tx,$base_url;
		$result =$db->query("select id,order_id,status,created_at from {$tx}test_order_logs where id={$id}");
		$testorderlog=$result->fetch_object();
		$html="<table class='table'>";
		$html.="<tr><th colspan=\"2\">TestOrderLog Show</th></tr>";
		$html.="<tr><th>Id</th><td>$testorderlog->id</td></tr>";
		$html.="<tr><th>Order Id</th><td>$testorderlog->order_id</td></tr>";
		$html.="<tr><th>Status</th><td>$testorderlog->status</td></tr>";
		$html.="<tr><th>Created At</th><td>$testorderlog->created_at</td></tr>";

		$html.="</table>";
		return $html;
	}
}
?>
