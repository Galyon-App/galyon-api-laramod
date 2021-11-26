<?php

namespace Galyon\Controllers;

use Galyon\Controllers\AppCore;

class Dashboard extends AppCore
{

    function __construct(){
		parent::__construct();
    }

    public function admin() {
        $is_admin = $this->is_authorized(true, ["admin"]);

        $orders = $this->Crud_model->sql_custom("SELECT COUNT(*) as counts FROM orders WHERE stage ='delivered' AND status = '1'", array(), "row" );
        $users = $this->Crud_model->sql_custom("SELECT COUNT(*) as counts FROM users WHERE type ='user' AND status = '1'", array(), "row" );
        $products = $this->Crud_model->sql_custom("SELECT COUNT(*) as counts FROM products WHERE template IS NOT NULL AND status = '1' AND deleted_at IS NULL", array(), "row" );
        $merchants = $this->Crud_model->sql_custom("SELECT COUNT(*) as counts FROM users WHERE type ='store' AND status = '1'", array(), "row" );

        $data = array(
            "orders" => $orders->counts,
            "users" => $users->counts,
            "products" => $products->counts,
            "merchants" => $merchants->counts
        );

        $this->json_response($data, true, "Successfully returned the admin dashboard data.");
    }

    public function merchant() {
        $auth = $this->is_authorized(true, ["store"]);
        $uuid =  $auth->uuid;

        $orders = $this->Crud_model->sql_custom(
            "SELECT DISTINCT(COUNT(users.uuid)) as counts 
            FROM users 
                INNER JOIN orders ON orders.uid = users.uuid 
                LEFT JOIN stores ON stores.uuid = orders.store_id
            WHERE users.uuid = '$uuid'
                AND orders.status = '1' 
                AND orders.deleted_at IS NULL 
                AND stores.status = '1' 
                AND stores.deleted_at IS NULL
                AND users.status = '1' 
                AND users.deleted_at IS NULL", array(), "row" );
        $users = $this->Crud_model->sql_custom("SELECT COUNT(*) as counts FROM orders INNER JOIN stores ON orders.store_id = stores.uuid LEFT JOIN users ON users.uuid = stores.owner WHERE users.uuid = '$uuid' AND orders.status = '1' AND orders.deleted_at IS NULL AND stores.status = '1' AND stores.deleted_at IS NULL", array(), "row" );
        $products = $this->Crud_model->sql_custom("SELECT COUNT(*) as counts FROM products INNER JOIN stores ON products.store_id = stores.uuid WHERE products.template IS NULL AND products.status = '1' AND products.deleted_at IS NULL AND stores.owner = '$uuid' AND stores.status = '1' AND stores.deleted_at IS NULL", array(), "row" );
        $stores = $this->Crud_model->sql_custom("SELECT COUNT(*) as counts FROM stores WHERE owner = '$uuid' AND status = '1' AND deleted_at IS NULL", array(), "row" );

        $data = array(
            "orders" => $orders->counts,
            "users" => $users->counts,
            "products" => $products->counts,
            "stores" => $stores->counts
        );

        $this->json_response($data, true, "Successfully returned the admin dashboard data.");
    }
}
