<?php
class TestOrderTrackingApi{
	public function __construct(){
	}
	function index(){
		echo json_encode(["test_order_tracking"=>TestOrderTracking::all()]);
	}
	function pagination($data){
		$page=$data["page"];
		$perpage=$data["perpage"];
		echo json_encode(["test_order_tracking"=>TestOrderTracking::pagination($page,$perpage),"total_records"=>TestOrderTracking::count()]);
	}
	function find($data){
		echo json_encode(["testordertracking"=>TestOrderTracking::find($data["id"])]);
	}
	function delete($data){
		TestOrderTracking::delete($data["id"]);
		echo json_encode(["success" => "yes"]);
	}
	function save($data,$file=[]){
		$testordertracking=new TestOrderTracking();
		$testordertracking->order_id=$data["order_id"];
		$testordertracking->status=$data["status"];
		$testordertracking->timestamp=$data["timestamp"];

		$testordertracking->save();
		echo json_encode(["success" => "yes"]);
	}
	function update($data,$file=[]){
		$testordertracking=new TestOrderTracking();
		$testordertracking->id=$data["id"];
		$testordertracking->order_id=$data["order_id"];
		$testordertracking->status=$data["status"];
		$testordertracking->timestamp=$data["timestamp"];

		$testordertracking->update();
		echo json_encode(["success" => "yes"]);
	}
}
?>
