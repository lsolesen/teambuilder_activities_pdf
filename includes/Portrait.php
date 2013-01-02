<?php
/**
 * @file
 */
require_once dirname(__FILE__) . '/Base.php';

class Teambuilder_Pdf_Portrait extends Teambuilder_Pdf_Base {

  protected $font = 'helvetica';
  protected $frontpage_font = 'helvetica';

  function __construct() {
    parent::__construct('P', 'mm', 'A4', TRUE, 'UTF-8', $disccache);
    $this->SetAutoPageBreak(FALSE);
    $this->SetMargins(0, 0, 0);
    $this->AliasNbPages(); 
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
  
  public function addActivityPage($activity) {
    global $base_url;
    $this->AddPage();

    $title = "  " . $activity->title;
    $description = strip_tags($activity->field_instruction[LANGUAGE_NONE][0]['value']);
    $keywords = array();
    foreach ($activity->taxonomy_vocabulary_1 as $taxonomy) {
      $keywords[] = $taxonomy->name;
    }

    $url = $base_url. '/node/' . $activity->nid;

    $title_size = 30;
    $this->SetFont('Helvetica', 'B', $title_size);

    $title_width = $this->GetStringWidth($title);
    
    if ($title_width > 200) {
      $title_size = 25;
    }

    $this->SetFont('Helvetica', 'B', $title_size);
    $this->SetTextColor(255, 255, 255);
    $this->Cell(0, 50, $title, null, 2, 'L', true);
    
    $this->SetLeftMargin(10);
    $this->SetRightMargin(10);
    $this->SetY(35);
    $this->SetFont('Helvetica', null, 10);
    $this->MultiCell(0, 5, implode($keywords, ", "), 0, 'L');

    if (!empty($activity->field_image[LANGUAGE_NONE][0])) {
      $x = 10;
      $y = 60;
      $width = 0;
      $spacing = 5;
      $count = 0;
      $picture_rows = 1;
      $presetname = 'activity';
      //$preset = imagecache_preset_by_name($presetname);

      foreach ($activity->field_image[LANGUAGE_NONE] as $image) {
        //$src = $image["filepath"];
        //$file = imagecache_create_path($presetname, $src);
        //if (file_exists($file) || imagecache_build_derivative($preset['actions'], $src, $file)) {
        if ($picture_filename = $this->getPictureFilename($presetname, $image['uri'])) {
          $size = getimagesize($picture_filename);
          if ($size[0] < $size[1]) {
            $orientation = 'portrait';
            $pic_width = 55;
            $new_line = 80;
            if ($count > 6) {
              break;
            }
          } else {
            $orientation = 'landscape';
            $pic_width = 80;
            $new_line = 50;
            if ($count > 4) {
              break;
            }
          }
          $width += $pic_width + $spacing;
          if ($width > 200) {
            $y += $new_line;
            $x = 10;
            $picture_rows++;
            $width = 0;
          }

          $this->Image($picture_filename, $x, $y, $pic_width, 0, '');
          $x += $pic_width + $spacing;
        }       
      }
    }

    $this->SetFont('Helvetica', null, 17);
    $this->setTextColor(0, 0, 0);

    if ($orientation == 'portrait') {
      if ($picture_rows == 1) {
        $this->setY(150);
      } else {
        $this->setY(230);
      }
    } else {
      if ($picture_rows == 1) {
        $this->setY(130);
      } else {
        $this->setY(200);
      }
    }

    $this->MultiCell(0, 8, $description, 0);

    $this->Image(dirname(__FILE__) . '/../vih_logo.jpg', 8, 261, 50, 0, '', 'http://vih.dk/');
    $this->Image(dirname(__FILE__) . '/../cc-by-sa_340x340.png', 190, 3, 17, 0, '');

    $this->SetFont('Helvetica', null, 8);
    $this->setY(280);
    $this->setX(7);
    $this->MultiCell(50, 8, $url, 0, 'C');

    $qr_file = $this->getBarcodePath($url, 200, 200);
    if ($qr_file !== false && file_exists($qr_file)) {
      $this->Image($qr_file, 160, 245, 45, 0, '');
    }
  }
}
