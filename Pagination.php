<?php

namespace impresja\impresja;

class Pagination
{

    public static function getPagination(int $count, string $url, string $after = '', int $perPage = null,): string
    {
        $perPage = $perPage ?? Application::$app->config->get('NA_STRONIE');
        $pageNumber = ceil($count / $perPage);
        if ($pageNumber < 2) return '';

        Application::$app->view->addMetaData(new metadata\MetaCssFile('/css/pagination.css'));
        $currentPage = Application::$app->request->getPageNumber();
        $prev = $currentPage > 2 ? '/' . $currentPage - 1 : '';
        $next = $currentPage <  $pageNumber ? '/' . $currentPage + 1 : '';
        $start = $currentPage - 3 <= 1 ? 1 : $currentPage - 3;
        $stop = $currentPage + 3 >= $pageNumber ? $pageNumber : $currentPage + 3;

        $pagination =  "<nav aria-label='Kolejne strony'><ul class='pagination'>";

        if ($currentPage != 1) {
            $pagination .=  "<li class='page-item'><a class='page-link' href='$url$after' aria-label='Next'><i class='fa-solid fa-angles-left'></i></a></li><li class='page-item'><a class='page-link' href='$url$prev$after' aria-label='Previous'><i class='fa-solid fa-angle-left'></i></a></li>";
        }
        for ($i = $start; $i <= $stop; $i++) {
            $number = $i != 1 ? "/$i" : '';
            $pagination .= "<li class='page-item'><a class='page-link" . self::setSelected($i, $currentPage) . "' href='$url$number$after'>$i</a></li>";
        }
        if ($currentPage != $pageNumber) {
            $pagination .= "<li class='page-item'><a class='page-link' href='$url$next$after' aria-label='Next'><i class='fa-solid fa-angle-right'></i></a></li><li class='page-item'><a class='page-link' href='$url/$pageNumber$after' aria-label='Next'><i class='fa-solid fa-angles-right'></i></a></li>";
        }
        $pagination .= "</ul></nav>";
        return $pagination;
    }

    private static function setSelected($i, $currentPage): string
    {
        if ($i == $currentPage) return " selected";
        return "";
    }
}
