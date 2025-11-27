<?php
class TestProductInventory extends Model implements JsonSerializable{
	public $id;
	public $product_id;
	public $variant_id;
	public $stock;
	public $created_at;
	public $updated_at;

	public function __construct(){
	}
	public function set($id,$product_id,$variant_id,$stock,$created_at,$updated_at){
		$this->id=$id;
		$this->product_id=$product_id;
		$this->variant_id=$variant_id;
		$this->stock=$stock;
		$this->created_at=$created_at;
		$this->updated_at=$updated_at;

	}
	public function save(){
		global $db,$tx;
		$db->query("insert into {$tx}test_product_inventory(product_id,variant_id,stock,created_at,updated_at)values('$this->product_id','$this->variant_id','$this->stock','$this->created_at','$this->updated_at')");
		return $db->insert_id;
	}
	public function update(){
		global $db,$tx;
		$db->query("update {$tx}test_product_inventory set product_id='$this->product_id',variant_id='$this->variant_id',stock='$this->stock',created_at='$this->created_at',updated_at='$this->updated_at' where id='$this->id'");
	}
	public static function delete($id){
		global $db,$tx;
		$db->query("delete from {$tx}test_product_inventory where id={$id}");
	}
	public function jsonSerialize(){
		return get_object_vars($this);
	}
	public static function all(){
		global $db,$tx;
		$result=$db->query("select id,product_id,variant_id,stock,created_at,updated_at from {$tx}test_product_inventory");
		$data=[];
		while($testproductinventory=$result->fetch_object()){
			$data[]=$testproductinventory;
		}
			return $data;
	}
	public static function pagination($page=1,$perpage=10,$criteria=""){
		global $db,$tx;
		$top=($page-1)*$perpage;
		$result=$db->query("select id,product_id,variant_id,stock,created_at,updated_at from {$tx}test_product_inventory $criteria limit $top,$perpage");
		$data=[];
		while($testproductinventory=$result->fetch_object()){
			$data[]=$testproductinventory;
		}
			return $data;
	}
	public static function count($criteria=""){
		global $db,$tx;
		$result =$db->query("select count(*) from {$tx}test_product_inventory $criteria");
		list($count)=$result->fetch_row();
			return $count;
	}
	public static function find($id){
		global $db,$tx;
		$result =$db->query("select id,product_id,variant_id,stock,created_at,updated_at from {$tx}test_product_inventory where id='$id'");
		$testproductinventory=$result->fetch_object();
			return $testproductinventory;
	}
	static function get_last_id(){
		global $db,$tx;
		$result =$db->query("select max(id) last_id from {$tx}test_product_inventory");
		$testproductinventory =$result->fetch_object();
		return $testproductinventory->last_id;
	}
	public function json(){
		return json_encode($this);
	}
	public function __toString(){
		return "		Id:$this->id<br> 
		Product Id:$this->product_id<br> 
		Variant Id:$this->variant_id<br> 
		Stock:$this->stock<br> 
		Created At:$this->created_at<br> 
		Updated At:$this->updated_at<br> 
";
	}

	//-------------HTML----------//

	static function html_select($name="cmbTestProductInventory"){
		global $db,$tx;
		$html="<select id='$name' name='$name'> ";
		$result =$db->query("select id,name from {$tx}test_product_inventory");
		while($testproductinventory=$result->fetch_object()){
			$html.="<option value ='$testproductinventory->id'>$testproductinventory->name</option>";
		}
		$html.="</select>";
		return $html;
	}
	static function html_table($page = 1,$perpage = 10,$criteria="",$action=true){
		global $db,$tx,$base_url;
		$count_result =$db->query("select count(*) total from {$tx}test_product_inventory $criteria ");
		list($total_rows)=$count_result->fetch_row();
		$total_pages = ceil($total_rows /$perpage);
		$top = ($page - 1)*$perpage;
		$result=$db->query("select id,product_id,variant_id,stock,created_at,updated_at from {$tx}test_product_inventory $criteria limit $top,$perpage");
		$html="<table class='table'>";
			$html.="<tr><th colspan='3'>".Html::link(["class"=>"btn btn-success","route"=>"testproductinventory/create","text"=>"New TestProductInventory"])."</th></tr>";
		if($action){
			$html.="<tr><th>Id</th><th>Product Id</th><th>Variant Id</th><th>Stock</th><th>Created At</th><th>Updated At</th><th>Action</th></tr>";
		}else{
			$html.="<tr><th>Id</th><th>Product Id</th><th>Variant Id</th><th>Stock</th><th>Created At</th><th>Updated At</th></tr>";
		}
		while($testproductinventory=$result->fetch_object()){
			$action_buttons = "";
			if($action){
				$action_buttons = "<td><div class='btn-group' style='display:flex;'>";
				$action_buttons.= Event::button(["name"=>"show", "value"=>"Show", "class"=>"btn btn-info", "route"=>"testproductinventory/show/$testproductinventory->id"]);
				$action_buttons.= Event::button(["name"=>"edit", "value"=>"Edit", "class"=>"btn btn-primary", "route"=>"testproductinventory/edit/$testproductinventory->id"]);
				$action_buttons.= Event::button(["name"=>"delete", "value"=>"Delete", "class"=>"btn btn-danger", "route"=>"testproductinventory/confirm/$testproductinventory->id"]);
				$action_buttons.= "</div></td>";
			}
			$html.="<tr><td>$testproductinventory->id</td><td>$testproductinventory->product_id</td><td>$testproductinventory->variant_id</td><td>$testproductinventory->stock</td><td>$testproductinventory->created_at</td><td>$testproductinventory->updated_at</td> $action_buttons</tr>";
		}
		$html.="</table>";
		$html.= pagination($page,$total_pages);
		return $html;
	}
	static function html_row_details($id){
		global $db,$tx,$base_url;
		$result =$db->query("select id,product_id,variant_id,stock,created_at,updated_at from {$tx}test_product_inventory where id={$id}");
		$testproductinventory=$result->fetch_object();
		$html="<table class='table'>";
		$html.="<tr><th colspan=\"2\">TestProductInventory Show</th></tr>";
		$html.="<tr><th>Id</th><td>$testproductinventory->id</td></tr>";
		$html.="<tr><th>Product Id</th><td>$testproductinventory->product_id</td></tr>";
		$html.="<tr><th>Variant Id</th><td>$testproductinventory->variant_id</td></tr>";
		$html.="<tr><th>Stock</th><td>$testproductinventory->stock</td></tr>";
		$html.="<tr><th>Created At</th><td>$testproductinventory->created_at</td></tr>";
		$html.="<tr><th>Updated At</th><td>$testproductinventory->updated_at</td></tr>";

		$html.="</table>";
		return $html;
	}
}
?>
