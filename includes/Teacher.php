<?php
/**
 * @file
 */
require_once dirname(__FILE__) . '/Base.php';

class Teambuilder_Pdf_Teacher extends Teambuilder_Pdf_Base {

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
    $description = check_markup($activity->body[LANGUAGE_NONE][0]['value'], 'filtered_html');
    //$description = strip_tags($activity->body[LANGUAGE_NONE][0]['value']);
    $keywords = array();
    foreach ($activity->taxonomy_vocabulary_1 as $taxonomy) {
      $keywords[] = $taxonomy->name;
    }
    $instruction = check_markup($activity->field_instruction[LANGUAGE_NONE][0]['value'], 'filtered_html');
    //$instruction = strip_tags($activity->field_instruction[LANGUAGE_NONE][0]['value']);
    $debriefing = strip_tags($activity->field_debriefing[LANGUAGE_NONE][0]['value']);
    $url = $base_url. '/node/' . $activity->nid;

    $where = strip_tags($activity->field_space[LANGUAGE_NONE][0]['value']);
    $what = implode($keywords, ", ");
    $who = strip_tags($activity->field_activity_who[LANGUAGE_NONE][0]['value']);
    $how_many = strip_tags($activity->field_groupsize[LANGUAGE_NONE][0]['value']);
    $materials = strip_tags($activity->field_materials[LANGUAGE_NONE][0]['value']);
    $duration = strip_tags($activity->field_time[LANGUAGE_NONE][0]['value']);

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
    $this->MultiCell(0, 5, implode($keywords, ", "), 0, 'L');

    $this->setTextColor(0, 0, 0);

    $this->SetFillColor(200, 200, 200);
    $this->SetFont('Helvetica', 'N', 7);

    $information = 'Hvor? ' . $where . '
Hvad? ' . $what . '
Hvem? ' . $who . '
Hvor mange? ' . $how_many . '
Materialer? ' . $materials  . '
Varighed? ' . $duration . '';

    $this->SetY(40);
    $this->SetX(10);
    $this->setCellPaddings(4, 4, 4, 4);
    $this->MultiCell(55, 0, $information, 1, 'L', false);
    $this->SetFont('Helvetica', null, 12);

    if (!empty($activity->field_image[LANGUAGE_NONE][0])) {
      $style = 'activity';
      $style_array = image_style_load($style);
      $old_orientation = '';
      $x = 10;
      $y = $this->GetY() + 5;
      $width = 0;
      $spacing = 3;
      $count = 0;
      $picture_rows = 1;
      $pr_row = 0;
      $no_of_pics = count($activity->field_image[LANGUAGE_NONE]);
      foreach ($activity->field_image[LANGUAGE_NONE] as $image) {
        $dst = image_style_path($style, $image['uri']);

        if (file_exists($dst) || image_style_create_derivative($style_array, $image['uri'], $dst)) {
          $file = image_style_path($style, $image['uri']);
          $size = getimagesize($file);
          if ($size[0] < $size[1]) {
            $orientation = 'portrait';
            if ($no_of_pics <= 2) {
              $pic_width = 55;
              $new_line = 45;
              $no_pr_row = 1;
            }
            elseif ($no_of_pics <= 3) {
              $pic_width = 50;
              $new_line = 40;
              $no_pr_row = 1;
            }
            else {
              $pic_width = 26;
              $new_line = 43;
              $no_pr_row = 2;
              if ($count > 8) {
                break;
              }
            }
          }
          else {
            $orientation = 'landscape';
            $pic_width = 55;
            $new_line = 20;
            $no_pr_row = 1;
            if ($count > 5) {
              break;
            }
          }

          if ($old_orientation != $orientation) {
            $pr_row = 0;
            if ($old_orientation == 'landscape') {
              $new_line += 40;
            }
          }

          $width += $pic_width + $spacing;

          if ($pr_row >= $no_pr_row) {
            $y += $new_line;
            $x = 10;
            $picture_rows++;
            $width = 0;
            $pr_row = 0;
          }

          $this->Image($file, $x, $y, $pic_width, 0, '');
          $x += $pic_width + $spacing;

          if ($no_pr_row == 1) {
            $y += $new_line;
            $x = 10;
          }
          $pr_row++;
          $old_orientation = $orientation;
          $count++;
        }
      }
    }

    // Content
    $this->setCellPaddings(0, 0, 0, 0);

    // Description
    $this->Ln(2);
    $this->SetFont('Helvetica', 'B', 14);
    $second_column_x = 70;
    $this->SetY(40);
    $this->SetX($second_column_x);
    $this->Cell(0, 4, t('Description'), 0, 1);
    $this->SetFont('Helvetica', 'N', 12);
    $this->SetX($second_column_x);
    $this->writeHTMLCell($w=0, $h=0, $x='', $y='', $description, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

    // Instructions
    $this->Ln(1);
    $this->SetFont('Helvetica', 'B', 14);
    $this->SetX($second_column_x);
    $this->Cell(0, 4, t('Instructions'), 0, 1);
    $this->SetFont('Helvetica', 'N', 12);
    $this->SetX($second_column_x);
    $this->writeHTMLCell($w=0, $h=0, $x='', $y='', $instruction, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

    // Debriefing
    if (!empty($debriefing)) {
      $this->Ln(1);
      $this->SetFont('Helvetica', 'B', 14);
      $this->SetX($second_column_x);
      $this->Cell(0, 4, t('Debriefing'), 0, 1);
      $this->SetFont('Helvetica', 'N', 12);
      $this->SetX($second_column_x);
      $this->writeHtmlCell($w=0, $h=0, $x='', $y='', $debriefing, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
    }

    $this->Image(dirname(__FILE__) . '/../vih_logo.jpg', 8, 261, 50, 0, '', 'http://vih.dk/');
    //$this->Image(dirname(__FILE__) . '/../cc-by-sa_340x340.png', 185, 4, 20, 0, '');

    $this->SetFont('Helvetica', null, 8);
    $this->setY(280);
    $this->setX(7);
    $this->MultiCell(50, 8, $url, 0, 'C');

    $qr_file = $this->getBarcodePath($url, 150, 150);

    if ($qr_file !== false && file_exists($qr_file)) {
      $this->Image($qr_file, 182, 3, 24, 0, '');
    }
  }
}
