<?php 

class VideoGrid {

    private $con, $userLoggedInObj;
    private $largeMode = false;
    private $gridClass = "videoGrid";

    public function __construct($con, $userLoggedInObj) {
        $this->con = $con;
        $this->userLoggedInObj = $userLoggedInObj;
    }

    public function create($videos, $title, $showFilter) {

        if ($videos == null) {
            $gridItems = $this->generateItems();
        } 
        else {
            $gridItems = $this->generateItemsFromVideos($videos);

        }

        $header = "";

        if ($title != null) {
            $header = $this->createGridHeader($title, $showFilter); 
        }
        
        return "$header
                <div class='$this->gridClass'>
                $gridItems
                </div>";
    }

    public function generateItems() {
        //prepare to show 15 suggested videos from the db for grid on the right of watch.php
        $query = $this->con->prepare("SELECT * FROM videos ORDER BY RAND() LIMIT 15");
        $query->execute();

        $elementsHTML = "";
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

            $video = new Video($this->con, $row, $this->userLoggedInObj);
            $item = new VideoGridItem($video, $this->largeMode);
            $elementsHTML .= $item->create();
        }

        return $elementsHTML;
    }

    public function generateItemsFromVideos($videos) {
        $elementsHTML = "";

        foreach ($videos as $video) {
            $item = new VideoGridItem($video, $this->largeMode);
            $elementsHTML .= $item->create();
        }

        return $elementsHTML;
    }

    public function createGridHeader($title, $showFilter) {
        $filter = "";
        
        if ($showFilter) {
            $link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            
            $urlArray = parse_url($link);
            $query = $urlArray["query"];

            parse_str($query, $params);

            
            unset($params["orderBy"]); //create new tag in link
            
            $newQuery = http_build_query($params);

            $newUrl = basename($_SERVER["PHP_SELF"]) . "?" . $newQuery; //whatever user passes into search is $newUrl
            //get base url
            //filter search by upload date and view count
            $filter = "<div class='right'>
                            <span>Order By:</span>
                            <a href='$newUrl&orderBy=uploadDate'>Upload date</a>
                            <a href='$newUrl&orderBy=views'>Most viewed</a>
                        </div>";
            
            //e.g. if search term = seal, the output will be:
            //e.g. youtube/search.php?term=seal&orderBy=views
            //e.g. youtube/search.php?term=seal&orderBy=uploadDate    

        }

        return "<div class='videoGridHeader'>
                    <div class='left'>
                        $title
                    </div>
                        $filter
                </div>";
    }

    public function createLarge($videos, $title, $showFilter) {
        $this->gridClass .= " large";
        $this->largeMode = true;
        return $this->create($videos, $title, $showFilter);
    }
}



?>