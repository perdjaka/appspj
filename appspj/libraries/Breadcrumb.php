<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Breadcrumb
 *
 * @author giant
 */
class Breadcrumb {
    protected $data = array();

    /**
     * Class Constructor
     *
     * @return void
     * @author Ibnu Daqiqil Id
     **/
    function __construct()
    {

    }

/**
     * add new crumb element
     *
     * @param  string $title The crumb title
     * @param  string $uri Crumb url path
     * @return void
     * @author Ibnu Daqiqil Id
     **/

    public function add($title, $uri='')
    {
            $this->data[] = array('title'=>$title, 'uri'=>$uri);
            return $this;
    }

    /**
     * Fetch crumb data
     *
     * @return void
     * @author Ibnu Daqiqil Id
     **/

    public function fetch()
    {
            return $this->data;
    }

    /**
     * Reset crumb data
     *
     * @return void
     * @author Ibnu Daqiqil Id
     **/
    public function reset()
    {
            $this->data = array();
    }


    /**
     * Dislpay all crumb element
     *
     * @param  string $home_site first path title
     * @param  string $id id of ul html
     * @return void
     * @author Ibnu Daqiqil Id
     **/
    public function show($home_site ="", $id = "crumbs"  )
    {
            $ci = &get_instance();
            $site = $home_site;
            $breadcrumbs = $this->data;
            $out  = '<ul id="'.$id.'">';
            if ($breadcrumbs && count($breadcrumbs) > 0) {
                    $out .= '<li><a class="pathway" href="' . base_url() .'"/>'. $site . '</a></li>';
                    $i=1;
                    $sep = '';
                    foreach ($breadcrumbs as $crumb):

                            if ($i != count($breadcrumbs)) {
                                    $out .= $sep . '<li><a class="pathway" href="' .site_url($crumb['uri']). '">'. $crumb['title'] .'</a></li>';
                            } else {
                                    $out .= $sep . '<li class="selected">'. $crumb['title'] .'</li>';
                            }
                            $i++;
                    endforeach;
            } else {
                    $out .= '<li class="selected">' . $site . '</li>';
            }
            $out .= '</ul>';
            return $out;	
    }
}
?>
