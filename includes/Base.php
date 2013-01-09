<?php
require_once libraries_get_path('tcpdf') . '/tcpdf.php';

abstract class Teambuilder_Pdf_Base extends TCPDF {
  protected $heading = 'Vejle Idrætshøjskole Teambuilder';
  protected $sub_title = '';
  protected $description = 'description';
  protected $logo;
  protected $events = array();
  protected $link = '';
  protected $base_url = '';
  protected $author;

  function setBaseUrl($base_url) {
    $this->base_url = $base_url;
  }
  
  function setAuthor($author) {
    $this->author = $author;
  }

  /**
   * @param string $logo Path to the logo
   */  
  function setLogo($logo, $link = null) {
    $this->logo = $logo;
    $this->link = $link;    
  }
  
  function setHeading($title) {
    $this->heading = $title;
  }

  function setSubtitle($title) {
    $this->sub_title = $title;
  }

  function setDescription($desc) {
    $this->description = $desc;
  }
    
  function Header() {
    // left blank to remove line in header added by TCPDF
  }
    
  function Footer() {
    // left blank to remove line in header added by TCPDF
  }
    
  function clearJavascript($s) {
    $do = true;
    while ($do) {
      $start = stripos($s, '<script');
      $stop = stripos($s, '</script>');
      if ((is_numeric($start)) && (is_numeric($stop))) {
        $s = substr($s, 0, $start) . substr($s, ($stop + strlen('</script>')));
      } 
      else {
        $do = false;
      }
    }
    return trim($s);
  }
 
  /**
   * Gets barcode file path
   *
   * @param string $url Registration url
   * @param integer $height Height
   * @param integer $width Width
   *
   * @return string or false
   */
  protected function getBarcodePath($url, $height, $width) {
    $chart = array(
      '#chart_id' => 'test_chart',
      '#type' => CHART_TYPE_QR,
      '#size' => chart_size(200, 200) 
    );
        
    $chart['#data'][] = '';
    $chart['#labels'][] = $url;
        
    return chart_copy($chart, 'my_chart_' . uniqid(), 'public://charts/');
  }
  
  protected function getPictureFilename($style_name, $uri) {
    $dest = image_style_path($style_name, $uri);
    if (!file_exists($dest)) {
      $style = image_style_load($style_name);
      image_style_create_derivative($style, $uri, $dest);
    }
    $picture_filename = drupal_realpath($dest);
    if (!file_exists($picture_filename)) {
      return FALSE;
    }
    return $picture_filename;
  }
}

