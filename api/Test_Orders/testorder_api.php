<?php
class TestOrderApi
{
	public function __construct() {}
	function index()
	{
		echo json_encode(["test_orders" => TestOrder::all()]);
	}
	function pagination($data)
	{
		$page = $data["page"];
		$perpage = $data["perpage"];
		echo json_encode(["test_orders" => TestOrder::pagination($page, $perpage), "total_records" => TestOrder::count()]);
	}
	function find($data)
	{
		echo json_encode(["testorder" => TestOrder::find($data["id"])]);
	}
	function delete($data)
	{
		TestOrder::delete($data["id"]);
		echo json_encode(["success" => "yes"]);
	}
	function save($data, $file = [])
	{
		// 1. Save Order (Parent)
		$testorder = new TestOrder();
		$testorder->order_number         = $data["orderNumber"] ?? '';
		$testorder->user_id              = $data["userId"] ?? 'guest';
		// placedAt: convert ISO to MySQL datetime
		$testorder->placed_at = isset($data["placedAt"]) && !empty($data["placedAt"])
			? date("Y-m-d H:i:s", strtotime($data["placedAt"]))
			: date("Y-m-d H:i:s");

		// fulfilledAt: store NULL if null or empty
		// Convert fulfilledAt to MySQL DATETIME or NULL
		if (isset($data["fulfilledAt"]) && $data["fulfilledAt"] !== null && $data["fulfilledAt"] !== '') {
			$testorder->fulfilled_at = date("Y-m-d H:i:s", strtotime($data["fulfilledAt"]));
		} else {
			$testorder->fulfilled_at = null; // store proper NULL
		}

		$testorder->payment_method = $data["paymentMethod"] ?? 'cod';
		$testorder->shipping_name        = $data["shipping_name"] ?? ($data["shippingAddress"]["name"] ?? '');
		$testorder->shipping_line1       = $data["shipping_line1"] ?? ($data["shippingAddress"]["line1"] ?? '');
		$testorder->shipping_line2       = $data["shipping_line2"] ?? ($data["shippingAddress"]["line2"] ?? '');
		$testorder->shipping_city        = $data["shipping_city"] ?? ($data["shippingAddress"]["city"] ?? '');
		$testorder->shipping_state       = $data["shipping_state"] ?? ($data["shippingAddress"]["state"] ?? '');
		$testorder->shipping_postal_code = $data["shipping_postal_code"] ?? ($data["shippingAddress"]["postalCode"] ?? '');
		$testorder->shipping_country     = $data["shipping_country"] ?? ($data["shippingAddress"]["country"] ?? '');
		$testorder->tracking_carrier     = $data["tracking"]['carrier'] ?? null;
		$testorder->tracking_number      = $data["tracking"]['trackingNumber'] ?? null;
		$testorder->status               = strtolower($data["status"] ?? 'pending'); // match DB ENUM
		$testorder->subtotal = $data["subtotal"] ?? 0;
		$testorder->shipping = $data["shipping"] ?? 0;
		$testorder->tax      = $data["tax"] ?? 0;
		$testorder->total    = $data["total"] ?? 0;

		$order_id = $testorder->save();

		// 2. Save Multiple Order Items
		$items = [];
		if (!empty($data["items"])) {
			if (is_array($data["items"])) {
				$items = $data["items"];
			} else {
				$items = json_decode($data["items"], true);
			}
		}

		if (!empty($items) && is_array($items)) {
			foreach ($items as $item) {
				$detail = new TestOrderdetail();
				$detail->order_id  = $order_id;
				$detail->product_id = $item["product_id"] ?? $item["id"] ?? null;
				$detail->title      = $item["title"] ?? "";
				$detail->quantity   = $item["quantity"] ?? 1;
				$detail->price      = $item["price"] ?? 0;

				$detail->save();
			}
		}

		// 3. Save Tracking Entry (First History Entry)
		if (!empty($data["status"])) {
			$tracking = new TestOrderTracking();
			$tracking->order_id  = $order_id;
			$tracking->status    = strtolower($data["status"]); // match DB ENUM
			$tracking->timestamp = $data["timestamp"] ?? date("Y-m-d H:i:s");
			$tracking->save();
		}

		$order = $testorder->find($order_id); // You need a getById() function

		// Insert initial log using the order's starting status (or 'pending' fallback)
		$initialStatus = strtolower($testorder->status ?? ($data['status'] ?? 'pending'));
		$testorder->addLog($order_id, $initialStatus);
		echo json_encode($order);
	}


	function update($data, $file = [])
	{
		$testorder = new TestOrder();
		$testorder->id = (int)($data["id"] ?? 0);
		$newStatus = strtolower($data["status"] ?? 'pending');

		// update main order
		$testorder->status = $newStatus;
		$testorder->updateStatus();

		// add a log row
		$testorder->addLog($testorder->id, $newStatus);

		// optional: if delivered, set fulfilled_at
		if ($newStatus === 'delivered') {
			$testorder->markFulfilled($testorder->id); // see helper below
		}

		echo json_encode(["success" => "yes", "data" => ["id" => $testorder->id, "status" => $newStatus]]);
	}
}
