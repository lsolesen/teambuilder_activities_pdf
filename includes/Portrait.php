<?php
/**
 * @file
 */
require_once dirname(__FILE__) . '/Base.php';

class Teambuilder_Pdf_Portrait extends Teambuilder_Pdf_Base {

  protected $font = 'helvetica';
  protected $frontpage_font = 'helvetica';

  function __construct($disccache = FALSE) {
    parent::__construct('P', 'mm', 'A4', TRUE, 'UTF-8', $disccache);
    $this->SetAutoPageBreak(FALSE);
    $this->SetMargins(0, 0, 0);
    $this->AliasNbPages(); 
  }

  public function addActivityPage($activity) {
    global $base_url;
    $this->AddPage();

    $title = "  " . $activity->title;
    $description = strip_tags($activity->field_instruction[LANGUAGE_NONE][0]['value']);
    
    /*
    $keywords = array();
    foreach ($activity->taxonomy_vocabulary_1 as $taxonomy) {
      $keywords[] = $taxonomy->name;
    }
    */

    $url = $base_url. '/node/' . $activity->nid;

    $title_size = 30;
    $this->SetFont('Helvetica', 'B', $title_size);

    $title_width = $this->GetStringWidth($title);
    
    if ($title_width > 200) {
      $title_size = 25;
    }

    $this->SetFont('Helvetica', 'B', $title_size);
    $this->SetTextColor(255, 255, 255);
    $this->Cell(0, 30, $title, null, 2, 'L', true);
    
    $this->SetLeftMargin(10);
    $this->SetRightMargin(10);
    $this->SetY(35);
    $this->SetFont('Helvetica', null, 10);
    // $this->MultiCell(0, 5, implode($keywords, ", "), 0, 'L');

    if (!empty($activity->field_image[LANGUAGE_NONE][0])) {
      $x = 10;
      $y = $this->GetY();
      $new_y = $y;
      $width = 0;
      $spacing = 5;
      $count = 0;
      $picture_rows = 1;
      $presetname = 'activity';
      $no_of_pics = count($activity->field_image[LANGUAGE_NONE]);

      foreach ($activity->field_image[LANGUAGE_NONE] as $image) {
        if ($picture_filename = $this->getPictureFilename($presetname, $image['uri'])) {
          list($w, $h, $type, $attr) = getimagesize($picture_filename);
          $ratio = $h/$w;

          if ($w < $h) {
            $orientation = 'portrait';
            if ($no_of_pics <= 2) {
              $pic_width = 80;
              $new_line = round($pic_width * $ratio + 5);
            }
            else {
              $pic_width = 55;
              $new_line = round($pic_width * $ratio + 5);
              if ($count > 6) {
                break;
              }
            }
          } else {
            $orientation = 'landscape';
            if ($no_of_pics == 1) {
              $pic_width = 190;
              $new_line = round($pic_width * $ratio + 5);
            }
            else {
              $pic_width = 80;
              $new_line = round($pic_width * $ratio + 5);
              if ($count > 4) {
                break;
              }
            }
          }
          $width += $pic_width + $spacing;
          if ($width > 200) {
            $y += $new_line;
            $x = 10;
            $picture_rows++;
            $width = 0;
            $new_y += $new_line;
          }
          $this->Image($picture_filename, $x, $y, $pic_width, 0, '');
          $x += $pic_width + $spacing;
        }
      }
      $this->setY($new_y + $new_line);
    }

    $this->SetFont('Helvetica', null, 14);
    $this->setTextColor(0, 0, 0);

    $this->MultiCell(0, 8, $description, 0, 'L', false, 1, '', '', true, 0, true);

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
