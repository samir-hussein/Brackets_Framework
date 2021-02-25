<?php

namespace App;

class Pagination
{
    private static $paginationDir = "ltr";
    private static $paginationNext = "next";
    private static $paginationPrevius = "previus";

    private static function changePaginationToAr()
    {
        self::$paginationDir = "rlt";
        self::$paginationNext = "التالى";
        self::$paginationPrevius = "السابق";
    }

    /**
     * Undocumented function
     *
     * @param array $info required (page,total_links,next,previus,href)
     * href given like that '/example/page/%s'
     * @param string $lang
     * @return void
     */
    public static function create(array $info, string $lang)
    {
        if ($lang == 'ar' || $lang == 'Ar') {
            self::changePaginationToAr();
        }
        $disabledpre = ($info['page'] <= 1) ? 'disabled' : '';
        $disablednext = ($info['page'] >= $info['total_links']) ? 'disabled' : '';
        $previusHref = sprintf($info['href'], $info['previus']);
        $nextHref = sprintf($info['href'], $info['next']);
        $lastPage = sprintf($info['href'], $info['total_links']);
        $firstPage = sprintf($info['href'], 1);
        $currentPage = sprintf($info['href'], $info['page']);

        if ($info['total_links'] > 1) {
            $activePage1 = ($info['page'] == 1) ? "active" : "";
            $activeLastPage = ($info['page'] == $info['total_links']) ? "active" : "";
            $pagination = "";
            $pagination .= "
            <section id='paginationpcView' style='direction: " . self::$paginationDir . "'>
                <nav aria-label='Page navigation example'>
                    <ul class='pagination justify-content-center p-0'>
                    <li class='page-item $disabledpre'>
                        <a class='page-link' href='$previusHref'>" . self::$paginationPrevius . "</a>
                    </li>
                    <li class='page-item $activePage1'><a class='page-link' href='$firstPage'>1</a></li>
                    ";

            if ($info['page'] < $info['total_links'] - 4) {

                if ($info['page'] >= 6) {
                    $pagination .= "<li class='page-item'><a class='page-link'>...</a></li>";
                }

                if ($info['page'] <= 5) {
                    $i = 2;
                    while ($i <= 5) {
                        $liHref = sprintf($info['href'], $i);
                        $active = ($i == $info['page']) ? "active" : "";
                        $pagination .= "
                        <li class='page-item $active'><a class='page-link' href='$liHref'>$i</a></li>
                        ";
                        $i++;
                    }
                } else {
                    $i = $info['page'] - 1;
                    while ($i < $info['page'] + 2) {
                        $liHref = sprintf($info['href'], $i);
                        $active = ($i == $info['page']) ? "active" : "";
                        $pagination .= "
                        <li class='page-item $active'><a class='page-link' href='$liHref'>$i</a></li>
                        ";
                        $i++;
                    }
                }

                $pagination .= "<li class='page-item'><a class='page-link'>...</a></li>";
            } else {
                if ($info['total_links'] > 8) {
                    $pagination .= "<li class='page-item'><a class='page-link'>...</a></li>";
                    $i = $info['total_links'] - 4;
                    while ($i < $info['total_links']) {
                        $liHref = sprintf($info['href'], $i);
                        $active = ($i == $info['page']) ? "active" : "";
                        $pagination .= "
                        <li class='page-item $active'><a class='page-link' href='$liHref'>$i</a></li>
                        ";
                        $i++;
                    }
                } else {
                    $i = 2;
                    while ($i < $info['total_links']) {
                        $liHref = sprintf($info['href'], $i);
                        $active = ($i == $info['page']) ? "active" : "";
                        $pagination .= "
                        <li class='page-item $active'><a class='page-link' href='$liHref'>$i</a></li>
                        ";
                        $i++;
                    }
                }
            }

            $pagination .= "
                <li class='page-item $activeLastPage'><a class='page-link' href='$lastPage'>" . $info['total_links'] . "</a></li>
                <li class='page-item $disablednext'>
                            <a class='page-link' href='$nextHref'>" . self::$paginationNext . "</a>
                        </li>
                        </ul>
                    </nav>
                </section>
            ";

            $paginationMobileView = "
            <section id='paginationMobileView' style='direction: " . self::$paginationDir . "'>
            <nav aria-label='Page navigation example'>
            <ul class='pagination justify-content-center p-0'>
            <li class='page-item $disabledpre'>
                <a class='page-link' href='$previusHref'>" . self::$paginationPrevius . "</a>
            </li>";
            if ($info['page'] != 1) {
                $paginationMobileView .= "
                <li class='page-item'><a class='page-link'>...</a></li>
                ";
            }
            $paginationMobileView .= "
            <li class='page-item active'><a class='page-link' href='$currentPage'>" . $info['page'] . "</a></li>";
            if ($info['page'] != $info['total_links']) {
                $paginationMobileView .= "
                <li class='page-item'><a class='page-link'>...</a></li>
                ";
            }
            $paginationMobileView .= "
            <li class='page-item $disablednext'>
                            <a class='page-link' href='$nextHref'>" . self::$paginationNext . "</a>
                        </li>
                        </ul>
                    </nav>
                </section>
            ";


            return [
                'pcView' => $pagination,
                'mobileView' => $paginationMobileView
            ];
        } else return null;
    }
}
