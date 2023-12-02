<?php 
require_once('./database/db_connection.php');

session_start();
$user = $_GET['user'] ?? false;

if(isset($_GET['page'])) {

    switch ($_GET['page']) {
        case 'home':
            $sql = "SELECT products.*, count(order_products.product_id) as sold FROM products LEFT JOIN order_products ON products.id = order_products.product_id GROUP BY products.id ORDER BY sold DESC LIMIT 4";
            $result = $conn->query($sql);
            $products = array();
        
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                    $products[count($products) - 1]['in_wishlist'] = false;
                }
            }
        
            if($user) {
                $sql = "SELECT * FROM user_wishlist WHERE user_id = $user";
                $result = $conn->query($sql);
                $wishlist = array();
                if($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $wishlist[] = $row;
                    }
                }
        
                foreach($products as $key => $product) {
                    $products[$key]['in_wishlist'] = false;
                    foreach($wishlist as $wish) {
                        if($product['ID'] == $wish['product_id']) {
                            $products[$key]['in_wishlist'] = true;
                        }
                    }
                }
            }
        
            echo json_encode($products);
            break;
        case 'wishlist':
            $sql = "SELECT products.* FROM products INNER JOIN user_wishlist ON products.id = user_wishlist.product_id WHERE user_wishlist.user_id = $user";
            $result = $conn->query($sql);
            $products = array();

            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                    $products[count($products) - 1]['in_wishlist'] = true;
                }
            }

            echo json_encode($products);
            break;
        case 'cart':
            $sql = "SELECT products.*, user_cart.quantity FROM products INNER JOIN user_cart ON products.id = user_cart.product_id WHERE user_cart.user_id = $user";
            $result = $conn->query($sql);
            $products = array();
            
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                    $products[count($products) - 1]['in_wishlist'] = false;
                }
            }
            
            echo json_encode(['status' => true, 'products' => $products]);
            break;
        default:
            $sql = "SELECT * FROM products";
            $result = $conn->query($sql);
            $products = array();
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                    $products[count($products) - 1]['in_wishlist'] = false;
                }
            }
        
            if($user) {
                $sql = "SELECT * FROM user_wishlist WHERE user_id = $user";
                $result = $conn->query($sql);
                $wishlist = array();
                if($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $wishlist[] = $row;
                    }
                }
        
                foreach($products as $key => $product) {
                    $products[$key]['in_wishlist'] = false;
                    foreach($wishlist as $wish) {
                        if($product['ID'] == $wish['product_id']) {
                            $products[$key]['in_wishlist'] = true;
                        }
                    }
                }
            }
        
            echo json_encode($products);
            break;
    }
} else {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    $products = array();
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
            $products[count($products) - 1]['in_wishlist'] = false;
        }
    }

    if($user) {
        $sql = "SELECT * FROM user_wishlist WHERE user_id = $user";
        $result = $conn->query($sql);
        $wishlist = array();
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $wishlist[] = $row;
            }
        }

        foreach($products as $key => $product) {
            $products[$key]['in_wishlist'] = false;
            foreach($wishlist as $wish) {
                if($product['ID'] == $wish['product_id']) {
                    $products[$key]['in_wishlist'] = true;
                }
            }
        }
    }

    echo json_encode($products);
}

?>