<?php
class TestDashboard extends Model
{
    public function __construct() {}

    public static function total_products()
    {
        global $db, $tx;
        $res = $db->query("SELECT COUNT(*) AS total FROM {$tx}test_products");
        return (int)($res->fetch_object()->total ?? 0);
    }

    public static function total_users($role_id)
    {
        global $db, $tx;
        $res = $db->query("SELECT COUNT(*) AS total FROM {$tx}test_users WHERE role_id={$role_id}");
        return (int)($res->fetch_object()->total ?? 0);
    }

    public static function total_orders($status = null)
    {
        global $db, $tx;
        $query = "SELECT COUNT(*) AS total FROM {$tx}test_orders";
        if ($status) $query .= " WHERE status='{$status}'";
        $res = $db->query($query);
        return (int)($res->fetch_object()->total ?? 0);
    }

    public static function total_revenue()
    {
        global $db, $tx;
        $res = $db->query("SELECT SUM(total) AS revenue FROM {$tx}test_orders WHERE status='delivered'");
        return (float)($res->fetch_object()->revenue ?? 0);
    }

    public static function sales_analytics($type = 'monthly')
    {
        global $db, $tx;
        $data = [];

        if ($type === 'monthly') {
            $res = $db->query("
            SELECT DATE_FORMAT(placed_at, '%Y-%m') AS ym,
                   COUNT(*) AS orders_count,
                   SUM(total) AS revenue
            FROM {$tx}test_orders
            GROUP BY DATE_FORMAT(placed_at, '%Y-%m')
            ORDER BY ym ASC
        ");

            while ($row = $res->fetch_object()) {
                $data[] = [
                    'ym' => $row->ym,
                    'orders_count' => (int)$row->orders_count,
                    'revenue' => (float)$row->revenue
                ];
            }
        } else { // daily
            $res = $db->query("
            SELECT DATE(placed_at) AS day,
                   COUNT(*) AS total_orders,
                   SUM(total) AS total_amount
            FROM {$tx}test_orders
            GROUP BY DATE(placed_at)
            ORDER BY day ASC
        ");

            while ($row = $res->fetch_object()) {
                $data[] = [
                    'day' => $row->day,
                    'total_orders' => (int)$row->total_orders,
                    'total_amount' => (float)$row->total_amount
                ];
            }
        }

        return $data;
    }

    public static function top_products()
    {
        global $db, $tx;
        $data = [];

        $res = $db->query("
    SELECT 
        p.id, 
        p.title AS name, 
        SUM(od.quantity) AS total_sold, 
        SUM(od.quantity * od.price) AS revenue
    FROM {$tx}test_orderdetails od
    JOIN {$tx}test_products p ON p.id = REPLACE(od.product_id, 'p-', '')
    GROUP BY p.id, p.title
    ORDER BY total_sold DESC
    LIMIT 5
");


        while ($row = $res->fetch_object()) {
            $data[] = [
                'id' => (int)$row->id,
                'name' => $row->name,
                'total_sold' => (int)$row->total_sold,
                'revenue' => (float)$row->revenue
            ];
        }

        return $data;
    }


    public static function customer_analytics($type = 'monthly') {
    global $db, $tx;
    $data = [];
    if($type === 'monthly') {
        $res = $db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS new_customers
            FROM {$tx}test_users
            WHERE role_id=3
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY ym ASC
        ");
        while($row = $res->fetch_object()) {
            $data[] = ['ym'=>$row->ym, 'new_customers'=>(int)$row->new_customers];
        }
    } else {
        $res = $db->query("
            SELECT DATE(created_at) AS day, COUNT(*) AS new_customers
            FROM {$tx}test_users
            WHERE role_id=3
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ");
        while($row = $res->fetch_object()) {
            $data[] = ['day'=>$row->day, 'new_customers'=>(int)$row->new_customers];
        }
    }
    return $data;
}


    public static function recent_orders($limit = 5)
    {
        global $db, $tx;
        $ordersRes = $db->query("
            SELECT o.*, u.name AS customer_name 
            FROM {$tx}test_orders o
            LEFT JOIN {$tx}test_users u ON u.id = o.user_id
            ORDER BY o.placed_at DESC
            LIMIT $limit
        ");
        $orders = [];
        while ($o = $ordersRes->fetch_assoc()) {
            $orders[] = [
                'id' => (int)$o['id'],
                'order_number' => $o['order_number'],
                'user_id' => (int)$o['user_id'],
                'customer_name' => $o['customer_name'] ?? '—',
                'status' => $o['status'],
                'total_amount' => (float)$o['total'],
                'created_at' => $o['placed_at']
            ];
        }
        return $orders;
    }
}
