<?php
class TestOrderdetailApi{
	public function __construct(){
	}
	function index(){
		echo json_encode(["test_orderdetails"=>TestOrderdetail::all()]);
	}
	function pagination($data){
		$page=$data["page"];
		$perpage=$data["perpage"];
		echo json_encode(["test_orderdetails"=>TestOrderdetail::pagination($page,$perpage),"total_records"=>TestOrderdetail::count()]);
	}
	function find($data){
		echo json_encode(["testorderdetail"=>TestOrderdetail::find($data["id"])]);
	}
	function delete($data){
		TestOrderdetail::delete($data["id"]);
		echo json_encode(["success" => "yes"]);
	}
	function save($data,$file=[]){
		$testorderdetail=new TestOrderdetail();
		$testorderdetail->order_id=$data["order_id"];
		$testorderdetail->product_id=$data["product_id"];
		$testorderdetail->title=$data["title"];
		$testorderdetail->quantity=$data["quantity"];

		$testorderdetail->save();
		echo json_encode(["success" => "yes"]);
	}
	function update($data,$file=[]){
		$testorderdetail=new TestOrderdetail();
		$testorderdetail->id=$data["id"];
		$testorderdetail->order_id=$data["order_id"];
		$testorderdetail->product_id=$data["product_id"];
		$testorderdetail->title=$data["title"];
		$testorderdetail->quantity=$data["quantity"];

		$testorderdetail->update();
		echo json_encode(["success" => "yes"]);
	}
}
?>
