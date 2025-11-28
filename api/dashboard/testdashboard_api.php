<?php
class TestDashboardApi
{
    public function __construct() {}

    public function index()
    {
        $data = [
            'success' => 'yes',

            // basic stats
            'totalProducts' => TestDashboard::total_products(),
            'totalOrders' => TestDashboard::total_orders(),
            'deliveredOrders' => TestDashboard::total_orders('delivered'),
            'pendingOrders' => TestDashboard::total_orders() - TestDashboard::total_orders('delivered'),
            'totalRevenue' => TestDashboard::total_revenue(),
            'totalAdmins' => TestDashboard::total_users(2),
            'totalCustomers' => TestDashboard::total_users(3),

            // sales analytics
            'salesPerMonth' => TestDashboard::sales_analytics(),       // monthly
            'salesPerDay' => TestDashboard::sales_analytics('daily'),  // daily

            // other analytics
            'topProducts' => TestDashboard::top_products(),
            'customerGrowth' => TestDashboard::customer_analytics(),
            'recentOrders' => TestDashboard::recent_orders(5)
        ];

        echo json_encode($data);
    }
}
