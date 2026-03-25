<?php
// database.php
class Database {
    private $host = "localhost";
    private $db_name = "blog";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}

function getPaginationLinks($current_page, $total_pages, $base_url, $search_query = '') {
    $links = '';
    
    if ($current_page > 1) {
        $prev_page = $current_page - 1;
        $links .= '<a href="' . $base_url . '?page=' . $prev_page . ($search_query ? '&search=' . urlencode($search_query) : '') . '">&laquo; Previous</a>';
    } else {
        $links .= '<span class="disabled">&laquo; Previous</span>';
    }
    
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $links .= '<span class="active">' . $i . '</span>';
        } else {
            $links .= '<a href="' . $base_url . '?page=' . $i . ($search_query ? '&search=' . urlencode($search_query) : '') . '">' . $i . '</a>';
        }
    }
    
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;
        $links .= '<a href="' . $base_url . '?page=' . $next_page . ($search_query ? '&search=' . urlencode($search_query) : '') . '">Next &raquo;</a>';
    } else {
        $links .= '<span class="disabled">Next &raquo;</span>';
    }
    
    return $links;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>