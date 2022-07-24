<?php

class Handler
{

    private $ImageBasepath = "https://mwonyaa.com/";
    public $pageNO;
    public $albumID;
    private $conn;

    public function __construct($con)
    {
        $this->conn = $con;
    }



    function allCombined()
    {

        $this->pageno = floatval($this->page);
        $no_of_records_per_page = 10;
        $offset = ($this->pageno - 1) * $no_of_records_per_page;

        $sql = "SELECT COUNT(DISTINCT(category_id)) as count FROM products WHERE published = 1 ORDER BY `products`.`featured` DESC limit 1";
        $result = mysqli_query($this->conn, $sql);
        $data = mysqli_fetch_assoc($result);
        $total_rows = floatval($data['count']);
        $total_pages = ceil($total_rows / $no_of_records_per_page);


        $categoryids = array();
        $menuCategory = array();
        $itemRecords = array();


        if ($this->pageno == 1) {

            // getSliderbanner
            $banners = new BusinessSettings($this->conn, 84);
            // $remove_brackets = str_replace(array('[', ']'), '', $banners->getHomeSliders());
            // $remove_braces = str_replace(array('"', '"'), '', $remove_brackets);
            // $str_arr = explode(",", $remove_braces);
            $str_arr = json_decode($banners->getHomeSliders());
            $slidermeta_img_path = array();


            foreach ($str_arr as $imageID) {
                $temp = array();
                $upload = new Upload($this->conn, $imageID);
                $filename = $this->imagePathRoot . $upload->getFile_name();
                $temp['id'] = 1;
                $temp['link'] = 2;
                $temp['filePath'] = $filename;
                array_push($slidermeta_img_path, $temp);
            }


            $slider_temps = array();
            $slider_temps['header_ad'] = "https://d2t03bblpoql2z.cloudfront.net/uploads/all/a8LWbZP0CdfEu5fw7uUPuSAaq6oYlC4jI7EtA6tq.gif";
            $slider_temps['sliderBanners'] = $slidermeta_img_path;
            array_push($menuCategory, $slider_temps);

            //end getSliderbanner



            //get featured categories

            $feat_CatIDs = array();
            $featuredCategory = array();


            $category_featured_stmt = "SELECT id FROM categories  WHERE featured = 1;";
            $feat_cat_id_result = mysqli_query($this->conn, $category_featured_stmt);

            while ($row = mysqli_fetch_array($feat_cat_id_result)) {

                array_push($feat_CatIDs, $row);
            }

            foreach ($feat_CatIDs as $row) {
                $category = new Category($this->conn, intval($row['id']));
                $temp = array();
                $temp['id'] = $category->getId();
                $temp['parent_id'] = $category->getParent_id();
                $temp['level'] = $category->getLevel();
                $temp['name'] = $category->getName();
                $temp['order_level'] = $category->getOrder_level();
                $temp['commision_rate'] = $category->getCommission_rate();
                $temp['banner'] = $category->getBanner();
                $temp['icon'] = $category->getIcon();
                $temp['featured'] = $category->getFeatured();
                $temp['top'] = $category->getTop();
                $temp['digital'] = $category->getDigital();
                $temp['slug'] = $category->getSlug();
                $temp['meta_title'] = $category->getMeta_title();
                $temp['meta_description'] = $category->getMeta_description();
                $temp['created_at'] = $category->getCreated_at();
                $temp['updated_at'] = $category->getUpdated_at();
                $temp['featuredCategoriesProduct'] = $category->getCategoryProducts();
                array_push($featuredCategory, $temp);
            }

            $feat_Cat_temps = array();
            $feat_Cat_temps['featuredCategories'] = $featuredCategory;
            array_push($menuCategory, $feat_Cat_temps);


            ///end featuredCategories


            //get Flash sales

            $feat_CatIDs = array();
            $featuredCategory = array();


            $category_featured_stmt = "SELECT id FROM flash_deals WHERE status = 1 ORDER BY id DESC";
            $feat_cat_id_result = mysqli_query($this->conn, $category_featured_stmt);
            while ($row = mysqli_fetch_array($feat_cat_id_result)) {
                array_push($feat_CatIDs, $row['id']);
            }
            foreach ($feat_CatIDs as $row) {
                $category = new FlashDeals($row, $this->conn);
                $temp = array();
                $temp['id'] = $category->getId();
                $temp['name'] = "Flash Deals";
                $temp['title'] = $category->getTitle();
                $temp['start_date'] = $category->getStartDate();
                $temp['end_date'] = $category->getEndDate();
                $temp['timeleft'] = $category->getTimeRemaining();
                $temp['status'] = $category->getStatus();
                $temp['featured'] = $category->getFeatured();
                $temp['background_color'] = $category->getBackgroundColor();
                $temp['text_color'] = $category->getTextColor();
                $temp['banner'] = $category->getBanner();
                $temp['slug'] = $category->getSlug();
                $temp['created_at'] = $category->getCreatedAt();
                $temp['updated_at'] = $category->getUpdatedAt();
                $temp['flashProducts'] = $category->getProducts();
                array_push($featuredCategory, $temp);
            }

            $feat_Cat_temps = array();
            $feat_Cat_temps['FlashDeals'] = $featuredCategory;
            array_push($menuCategory, $feat_Cat_temps);
            ///end Flash sales

            // Todays Deal Begin

            $bestsellingProductsID = array();
            $bestSellingProducts = array();
            $category_stmts = "SELECT DISTINCT(id) FROM products   WHERE published = 1 AND `todays_deal` = 1 ORDER BY `products`.`created_at` DESC  LIMIT 8";
            $menu_type_id_results = mysqli_query($this->conn, $category_stmts);

            while ($row = mysqli_fetch_array($menu_type_id_results)) {

                array_push($bestsellingProductsID, $row);
            }

            foreach ($bestsellingProductsID as $row) {
                $product = new Product($this->conn, intval($row['id']));
                $temp = array();
                $temp['id'] = $product->getId();
                $temp['name'] = $product->getName();
                $temp['category_id'] = $product->getCategory_id();
                $temp['photos'] = $product->getPhotos();
                $temp['thumbnail_img'] = $product->getThumbnail_img();
                $temp['unit_price'] = $product->getUnit_price();
                $temp['discount'] = $product->getDiscount();
                $temp['purchase_price'] = $product->getPurchase_price();
                $temp['meta_title'] = $product->getMeta_title();
                $temp['meta_description'] = $product->getMeta_description();
                $temp['meta_img'] = $product->getMeta_img();
                $temp['min_qtn'] = $product->getMin_qty();
                $temp['published'] = $product->getPublished();

                array_push($bestSellingProducts, $temp);
            }


            $best_temps = array();
            $best_temps['id'] = 100;
            $best_temps['parent_id'] = 100;
            $best_temps['level'] = 1;
            $best_temps['name'] = "Today's Deal";
            $best_temps['order_level'] = 0;
            $best_temps['commision_rate'] = 0;
            $best_temps['banner'] = null;
            $best_temps['icon'] = null;
            $best_temps['featured'] = 0;
            $best_temps['top'] = 0;
            $best_temps['digital'] = 0;
            $best_temps['slug'] = "Today's Deal";
            $best_temps['meta_title'] = null;
            $best_temps['meta_description'] = null;
            $best_temps['created_at'] = "10 Jul 2021";
            $best_temps['updated_at'] = "10 Jul 2021";
            $best_temps['products'] = $bestSellingProducts;
            array_push($menuCategory, $best_temps);

            // end Todays Deal  Fetch

            // Featured Products Begin

            $bestsellingProductsID = array();
            $bestSellingProducts = array();
            $category_stmts = "SELECT DISTINCT(id) FROM products   WHERE published = 1 AND `featured` = 1 ORDER BY `products`.`created_at` DESC  LIMIT 8";
            $menu_type_id_results = mysqli_query($this->conn, $category_stmts);

            while ($row = mysqli_fetch_array($menu_type_id_results)) {

                array_push($bestsellingProductsID, $row);
            }

            foreach ($bestsellingProductsID as $row) {
                $product = new Product($this->conn, intval($row['id']));
                $temp = array();
                $temp['id'] = $product->getId();
                $temp['name'] = $product->getName();
                $temp['category_id'] = $product->getCategory_id();
                $temp['photos'] = $product->getPhotos();
                $temp['thumbnail_img'] = $product->getThumbnail_img();
                $temp['unit_price'] = $product->getUnit_price();
                $temp['discount'] = $product->getDiscount();
                $temp['purchase_price'] = $product->getPurchase_price();
                $temp['meta_title'] = $product->getMeta_title();
                $temp['meta_description'] = $product->getMeta_description();
                $temp['meta_img'] = $product->getMeta_img();
                $temp['min_qtn'] = $product->getMin_qty();
                $temp['published'] = $product->getPublished();

                array_push($bestSellingProducts, $temp);
            }

            $best_temps = array();
            $best_temps['id'] = 100;
            $best_temps['parent_id'] = 100;
            $best_temps['level'] = 1;
            $best_temps['name'] = "Featured Products";
            $best_temps['order_level'] = 0;
            $best_temps['commision_rate'] = 0;
            $best_temps['banner'] = null;
            $best_temps['icon'] = null;
            $best_temps['featured'] = 0;
            $best_temps['top'] = 0;
            $best_temps['digital'] = 0;
            $best_temps['slug'] = "Featured Products";
            $best_temps['meta_title'] = null;
            $best_temps['meta_description'] = null;
            $best_temps['created_at'] = "10 Jul 2021";
            $best_temps['updated_at'] = "10 Jul 2021";
            $best_temps['products'] = $bestSellingProducts;
            array_push($menuCategory, $best_temps);

            // end Featured Products Fetch


            //BEST selling  fetch Begin

            $bestsellingProductsID = array();
            $bestSellingProducts = array();
            $category_stmts = "SELECT DISTINCT(id) FROM products  WHERE published = 1 ORDER BY `products`.`num_of_sale` DESC  LIMIT 8";
            $menu_type_id_results = mysqli_query($this->conn, $category_stmts);

            while ($row = mysqli_fetch_array($menu_type_id_results)) {

                array_push($bestsellingProductsID, $row);
            }

            foreach ($bestsellingProductsID as $row) {
                $product = new Product($this->conn, intval($row['id']));
                $temp = array();
                $temp['id'] = $product->getId();
                $temp['name'] = $product->getName();
                $temp['category_id'] = $product->getCategory_id();
                $temp['photos'] = $product->getPhotos();
                $temp['thumbnail_img'] = $product->getThumbnail_img();
                $temp['unit_price'] = $product->getUnit_price();
                $temp['discount'] = $product->getDiscount();
                $temp['purchase_price'] = $product->getPurchase_price();
                $temp['meta_title'] = $product->getMeta_title();
                $temp['meta_description'] = $product->getMeta_description();
                $temp['meta_img'] = $product->getMeta_img();
                $temp['min_qtn'] = $product->getMin_qty();
                $temp['published'] = $product->getPublished();

                array_push($bestSellingProducts, $temp);
            }

            $best_temps = array();
            $best_temps['id'] = 100;
            $best_temps['parent_id'] = 100;
            $best_temps['level'] = 1;
            $best_temps['name'] = "Best Selling";
            $best_temps['order_level'] = 0;
            $best_temps['commision_rate'] = 0;
            $best_temps['banner'] = null;
            $best_temps['icon'] = null;
            $best_temps['featured'] = 0;
            $best_temps['top'] = 0;
            $best_temps['digital'] = 0;
            $best_temps['slug'] = "Best Selling";
            $best_temps['meta_title'] = null;
            $best_temps['meta_description'] = null;
            $best_temps['created_at'] = "10 Jul 2021";
            $best_temps['updated_at'] = "10 Jul 2021";
            $best_temps['products'] = $bestSellingProducts;
            array_push($menuCategory, $best_temps);

            // end Best Selling Fetch
        }


        //fetch other categories Begin

        $category_stmt = "SELECT DISTINCT(category_id) FROM products  WHERE published = 1 ORDER BY `products`.`featured` DESC LIMIT " . $offset . "," . $no_of_records_per_page . "";
        $menu_type_id_result = mysqli_query($this->conn, $category_stmt);

        while ($row = mysqli_fetch_array($menu_type_id_result)) {

            array_push($categoryids, $row);
        }

        foreach ($categoryids as $row) {
            $category = new Category($this->conn, intval($row['category_id']));
            $temp = array();
            $temp['id'] = $category->getId();
            $temp['parent_id'] = $category->getParent_id();
            $temp['level'] = $category->getLevel();
            $temp['name'] = $category->getName();
            $temp['order_level'] = $category->getOrder_level();
            $temp['commision_rate'] = $category->getCommission_rate();
            $temp['banner'] = $category->getBanner();
            $temp['icon'] = $category->getIcon();
            $temp['featured'] = $category->getFeatured();
            $temp['top'] = $category->getTop();
            $temp['digital'] = $category->getDigital();
            $temp['slug'] = $category->getSlug();
            $temp['meta_title'] = $category->getMeta_title();
            $temp['meta_description'] = $category->getMeta_description();
            $temp['created_at'] = $category->getCreated_at();
            $temp['updated_at'] = $category->getUpdated_at();
            $temp['products'] = $category->getCategoryProducts();
            array_push($menuCategory, $temp);
        }

        $itemRecords["version"] = $this->version;
        $itemRecords["page"] = $this->pageno;
        $itemRecords["categories"] = $menuCategory;
        $itemRecords["total_pages"] = $total_pages;
        $itemRecords["total_results"] = $total_rows;

        return $itemRecords;
    }


    function searchHomePage()
    {
        $this->pageno = floatval($this->page);
        $no_of_records_per_page = 10;
        $offset = ($this->pageno - 1) * $no_of_records_per_page;

        $sql = "SELECT COUNT(DISTINCT(category_id)) as count FROM products WHERE published = 1 ORDER BY `products`.`featured` DESC limit 1";
        $result = mysqli_query($this->conn, $sql);
        $data = mysqli_fetch_assoc($result);
        $total_rows = floatval($data['count']);
        $total_pages = ceil($total_rows / $no_of_records_per_page);

        $menuCategory = array();
        $itemRecords = array();

        //  popular search Begin
        $bestsellingProductsID = array();
        $bestSellingProducts = array();
        $category_stmts = "SELECT `id`, `query`, `count`, `created_at`, `updated_at` FROM `searches` ORDER BY count DESC LIMIT 30";
        $menu_type_id_results = mysqli_query($this->conn, $category_stmts);

        while ($row = mysqli_fetch_array($menu_type_id_results)) {

            array_push($bestsellingProductsID, $row);
        }

        foreach ($bestsellingProductsID as $row) {
            $temp = array();
            $temp['id'] = $row['id'];
            $temp['query'] = $row['query'];
            $temp['count'] = $row['count'];
            $temp['created_at'] = $row['created_at'];
            $temp['updated_at'] = $row['updated_at'];
            array_push($bestSellingProducts, $temp);
        }


        $slider_temps = array();
        $slider_temps['popularSearch'] = $bestSellingProducts;
        array_push($menuCategory, $slider_temps);

        // end popular search  Fetch


        //get search featured categories

        $feat_CatIDs = array();
        $featuredCategory = array();


        $category_featured_stmt = "SELECT id FROM categories  WHERE featured = 1;";
        $feat_cat_id_result = mysqli_query($this->conn, $category_featured_stmt);

        while ($row = mysqli_fetch_array($feat_cat_id_result)) {

            array_push($feat_CatIDs, $row);
        }

        foreach ($feat_CatIDs as $row) {
            $category = new Category($this->conn, intval($row['id']));
            $temp = array();
            $temp['id'] = $category->getId();
            $temp['parent_id'] = $category->getParent_id();
            $temp['level'] = $category->getLevel();
            $temp['name'] = $category->getName();
            $temp['order_level'] = $category->getOrder_level();
            $temp['commision_rate'] = $category->getCommission_rate();
            $temp['banner'] = $category->getBanner();
            $temp['icon'] = $category->getIcon();
            $temp['featured'] = $category->getFeatured();
            $temp['top'] = $category->getTop();
            $temp['digital'] = $category->getDigital();
            $temp['slug'] = $category->getSlug();
            $temp['meta_title'] = $category->getMeta_title();
            $temp['meta_description'] = $category->getMeta_description();
            $temp['created_at'] = $category->getCreated_at();
            $temp['updated_at'] = $category->getUpdated_at();
            $temp['featuredCategoriesProduct'] = null;
            array_push($featuredCategory, $temp);
        }

        $feat_Cat_temps = array();
        $feat_Cat_temps['featuredCategories'] = $featuredCategory;
        array_push($menuCategory, $feat_Cat_temps);


        $itemRecords["page"] = 1;
        $itemRecords["searchCategoriees"] = $menuCategory;
        $itemRecords["total_pages"] = 1;
        $itemRecords["total_results"] = 14;

        return $itemRecords;
    }

    function searchFullText()
    {
        $search_algorithm = "fulltext";
        // SELECT * FROM products WHERE MATCH (name) AGAINST ('cooking oil')

        // create the base variables for building the search query
        $search_string = "SELECT * FROM products WHERE published = 1 AND ";
        $display_words = "";

        // format each of search keywords into the db query to be run
        $search_string .= "MATCH (name,tags) AGAINST ('" . $this->query . "' IN NATURAL LANGUAGE MODE)";
        $display_words .= $this->query . ' ';

//        echo $search_string;
        // run the query in the db and search through each of the records returned
        $query = mysqli_query($this->conn, $search_string);
        $result_count = mysqli_num_rows($query);

        $this->pageno = floatval($this->page);
        $no_of_records_per_page = 10;
        $offset = ($this->pageno - 1) * $no_of_records_per_page;


        $total_rows = floatval(number_format($result_count));
        $total_pages = ceil($total_rows / $no_of_records_per_page);


        $itemRecords = array();


        // check if the search query returned any results
        if ($result_count > 0) {

            $categoryids = array();
            $menuCategory = array();


            $category_stmt = $search_string . " LIMIT " . $offset . "," . $no_of_records_per_page . "";


            $menu_type_id_result = mysqli_query($this->conn, $category_stmt);

            while ($row = mysqli_fetch_array($menu_type_id_result)) {

                array_push($categoryids, $row);
            }

            foreach ($categoryids as $row) {
                $product = new Product($this->conn, intval($row['id']));
                $temp = array();
                $temp['id'] = $product->getId();
                $temp['name'] = $product->getName();
                $temp['category_id'] = $product->getCategory_id();
                $temp['photos'] = $product->getPhotos();
                $temp['thumbnail_img'] = $product->getThumbnail_img();
                $temp['unit_price'] = intVal($product->getUnit_price());
                $temp['discount'] = intVal($product->getDiscount());
                $temp['purchase_price'] = intVal($product->getPurchase_price());
                $temp['meta_title'] = $product->getMeta_title();
                $temp['meta_description'] = $product->getMeta_description();
                $temp['meta_img'] = $product->getMeta_img();
                $temp['min_qtn'] = $product->getMin_qty();
                $temp['published'] = $product->getPublished();
                array_push($menuCategory, $temp);
            }


            $itemRecords["page"] = $this->pageno;
            $itemRecords["searchTerm"] = $display_words;
            $itemRecords["algorithm"] = $search_algorithm;
            $itemRecords["products"] = $menuCategory;
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;

        } else {
            $itemRecords["page"] = $this->pageno;
            $itemRecords["searchTerm"] = $display_words;
            $itemRecords["algorithm"] = $search_algorithm;
            $itemRecords["products"] = null;
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;
        }

        return $itemRecords;
    }


    //get selected Product details and similar product
    function readSelectedAlbum()
    {

        $itemRecords = array();

        $this->albumID = htmlspecialchars(strip_tags($_GET["albumID"]));
        $this->pageNO = htmlspecialchars(strip_tags($_GET["page"]));

        if ($this->albumID) {
            $this->pageNO = floatval($this->pageNO);
            $no_of_records_per_page = 6;
            $offset = ($this->pageNO - 1) * $no_of_records_per_page;

            $sql = "SELECT COUNT(*) as count FROM songs WHERE album = '". $this->albumID . "'  limit 1";
            $result = mysqli_query($this->conn, $sql);
            $data = mysqli_fetch_assoc($result);
            $total_rows = floatval($data['count']);
            $total_pages = ceil($total_rows / $no_of_records_per_page);

            $itemRecords["page"] = $this->pageNO;
            $itemRecords["selectedAlbum"] = array();
            $itemRecords["total_pages"] = $total_pages;
            $itemRecords["total_results"] = $total_rows;
            $album = new Album($this->conn, $this->albumID);


            if ($this->pageNO == 1) {

                if($album){
                    $temp = array();
                    $temp['id'] = $album->getId();
                    $temp['title'] = $album->getTitle();
                    $temp['artistName'] = $album->getArtist()->getName();
                    $temp['artistID'] = $album->getArtistId();
                    $temp['genreID'] = $album->getGenre()->getGenreid();
                    $temp['genreName'] = $album->getGenre()->getGenre();
                    $temp['tracks_count'] = $album->getNumberOfSongs();
                    $temp['artworkPath'] = $album->getArtworkPath();
                    $temp['description'] = $album->getDescription();
                    $temp['datecreated'] = $album->getDatecreated();
                    $temp['totaltrackplays'] = $album->getTotaltrackplays();
                    $temp['tag'] = $album->getTag();
                    $temp['trackPath'] = $album->getSongPaths();

                    array_push($itemRecords["selectedAlbum"], $temp);

                }

            }


            // get products id from the same cat
            $same_cat_IDs = $album->getSongIds();

            $allProducts = array();

            foreach ($same_cat_IDs as $row) {
                $song = new Song($this->conn,$row);
                $temp = array();
                $temp['id'] = $song->getId();
                $temp['title'] = $song->getTitle();
                $temp['artist'] = $song->getArtist()->getName();
                $temp['album'] = $song->getAlbum()->getTitle();
                $temp['artworkPath'] = $song->getAlbum()->getArtworkPath();
                $temp['genre'] = $song->getGenre()->getGenre();
                $temp['genreID'] = $song->getGenre()->getGenreid();
                $temp['duration'] = $song->getDuration();
                $temp['path'] = $song->getPath();
                $temp['totalplays'] = $song->getPlays();
                $temp['weeklyplays'] = $song->getWeeklyplays();


                array_push($allProducts, $temp);
            }

            $slider_temps = array();
            $slider_temps['Tracks'] = $allProducts;
            array_push($itemRecords['selectedAlbum'], $slider_temps);



        }


        return $itemRecords;
    }



}
