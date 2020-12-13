<?php

namespace core;

class Pagination
{
    private static $paginationDir = "ltr";
    private static $paginationNext = "next";
    private static $paginationPrevius = "previus";

    public static function changePaginationToAr()
    {
        self::$paginationDir="rlt";
        self::$paginationNext="التالى";
        self::$paginationPrevius="السابق";
    }

    public static function pagination($limit,$total_recordes,$page,$href)
    {
        $total_links = ceil($total_recordes / $limit);
        $next = ($page == $total_links)? $total_links:$page + 1;
        $previus = ($page == 1)? 1: $page - 1;
        $disabledpre = ($page == 1)? 'disabled':'';
        $disablednext = ($page == $total_links)? 'disabled':'';
        $previusHref = sprintf($href,$previus);
        $nextHref = sprintf($href,$next);
        $totalLinksHref = sprintf($href,$total_links);
        $firstPage = sprintf($href,1);
        $currentPage = sprintf($href,$page);

        if($total_links > 1){
            $activePage1 = ($page == 1)? "active":"";
            $activeLastPage = ($page == $total_links)? "active":"";
            $pagination = "";
            $pagination .= "
            <section id='paginationpcView' style='direction: ".self::$paginationDir."'>
                <nav aria-label='Page navigation example'>
                    <ul class='pagination justify-content-center p-0'>
                    <li class='page-item $disabledpre'>
                        <a class='page-link' href='$previusHref'>".self::$paginationPrevius."</a>
                    </li>
                    <li class='page-item $activePage1'><a class='page-link' href='$firstPage'>1</a></li>
                    ";

            if($page < $total_links - 4){

                if($page >= 6){
                    $pagination .= "<li class='page-item'><a class='page-link'>...</a></li>";
                }

                if($page <= 5){
                    $i = 2;
                    while($i <= 5){
                        $liHref = sprintf($href,$i);
                        $active = ($i == $page)? "active":"";
                        $pagination .= "
                        <li class='page-item $active'><a class='page-link' href='$liHref'>$i</a></li>
                        ";
                        $i++;
                    }
                }
                else{
                    $i = $page - 1;
                    while($i < $page + 2){
                        $liHref = sprintf($href,$i);
                        $active = ($i == $page)? "active":"";
                        $pagination .= "
                        <li class='page-item $active'><a class='page-link' href='$liHref'>$i</a></li>
                        ";
                        $i++;
                    }
                }

                $pagination .= "<li class='page-item'><a class='page-link'>...</a></li>";
            }
            else{
                if($total_links > 8){
                    $pagination .= "<li class='page-item'><a class='page-link'>...</a></li>";
                    $i = $total_links - 4;
                    while($i < $total_links){
                        $liHref = sprintf($href,$i);
                        $active = ($i == $page)? "active":"";
                        $pagination .= "
                        <li class='page-item $active'><a class='page-link' href='$liHref'>$i</a></li>
                        ";
                        $i++;
                    }
                }
                else{
                    $i = 2;
                    while($i < $total_links){
                        $liHref = sprintf($href,$i);
                        $active = ($i == $page)? "active":"";
                        $pagination .= "
                        <li class='page-item $active'><a class='page-link' href='$liHref'>$i</a></li>
                        ";
                        $i++;
                    }
                }
            }
            
            $pagination .="
                <li class='page-item $activeLastPage'><a class='page-link' href='$totalLinksHref'>$total_links</a></li>
                <li class='page-item $disablednext'>
                            <a class='page-link' href='$nextHref'>".self::$paginationNext."</a>
                        </li>
                        </ul>
                    </nav>
                </section>
            ";

            $paginationMobileView = "
            <section id='paginationMobileView' style='direction: ".self::$paginationDir."'>
            <nav aria-label='Page navigation example'>
            <ul class='pagination justify-content-center p-0'>
            <li class='page-item $disabledpre'>
                <a class='page-link' href='$previusHref'>".self::$paginationPrevius."</a>
            </li>";
            if($page != 1){
                $paginationMobileView .= "
                <li class='page-item'><a class='page-link'>...</a></li>
                ";
            }
            $paginationMobileView .="
            <li class='page-item active'><a class='page-link' href='$currentPage'>$page</a></li>";
            if($page != $total_links){
                $paginationMobileView .= "
                <li class='page-item'><a class='page-link'>...</a></li>
                ";
            }
            $paginationMobileView .="
            <li class='page-item $disablednext'>
                            <a class='page-link' href='$nextHref'>".self::$paginationNext."</a>
                        </li>
                        </ul>
                    </nav>
                </section>
            ";


            return [
                'pagination' => $pagination,
                'paginationMobileView' => $paginationMobileView
            ];
        }
        else return false;
    }
}

