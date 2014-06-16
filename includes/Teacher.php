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
    $description = strip_tags($activity->body[LANGUAGE_NONE][0]['value']);
    $keywords = array();
    foreach ($activity->taxonomy_vocabulary_1 as $taxonomy) {
      $keywords[] = $taxonomy->name;
    }
    $instruction = strip_tags($activity->field_instruction[LANGUAGE_NONE][0]['value']);
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

    if (!empty($activity->field_image[LANGUAGE_NONE][0]['uri'])) {
      $x = 10;
      $y = $this->GetY();
      $new_y = $y;
      $presetname = 'activity_landscape';
      $pic_width = 90;
      if ($picture_filename = $this->getPictureFilename($presetname, $activity->field_image[LANGUAGE_NONE][0]['uri'])) {
        $this->Image($picture_filename, $x, $y, $pic_width, 0, '');
        $this->SetY($new_y + $new_line);
      }
    }

    $this->setTextColor(0, 0, 0);

    $this->SetFillColor(200, 200, 200);
    $this->SetFont('Helvetica', 'N', 7);

    $information = 'Hvor? ' . $where . '
Hvad? ' . $what . '
Hvem? ' . $who . '
Hvor mange? ' . $how_many . '
Materialer? ' . $materials  . '
Varighed? ' . $duration . '';

    // TODO WHAT SHOULD THIS BE?
    $cell_width = '';

    $this->SetY(40);
    $this->SetX(105);
    $this->setCellPaddings(4, 4, 4, 4);
    $this->MultiCell($cell_width, 60, $information, 1, 'L', false);
    $this->SetFont('Helvetica', null, 12);
    $this->setCellPaddings(0, 0, 0, 0);
    $this->Ln(2);
    $this->SetFont('Helvetica', 'B', 14);
    $this->MultiCell($cell_width, 4, t('Description'), 0, 'L', false, 1, '', '', true, 0, true);
    $this->SetFont('Helvetica', 'N', 12);
    $this->MultiCell($cell_width, 4, $description, 0, 'L', false, 1, '', '', true, 0, true);
    $this->Ln(2);
    $this->SetFont('Helvetica', 'B', 14);
    $this->MultiCell($cell_width, 4, t('Instructions'), 0, 'L', false, 1, '', '', true, 0, true);
    $this->SetFont('Helvetica', 'N', 12);
    $this->MultiCell($cell_width, 4, $instruction, 0, 'L', false, 1, '', '', true, 0, true);

    if (!empty($debriefing)) {
      $this->Ln(2);
      $this->SetFont('Helvetica', 'B', 14);
      $this->MultiCell($cell_width, 4, t('Debriefing'), 0, 'L', false, 1, '', '', true, 0, true);
      $this->SetFont('Helvetica', 'N', 12);
      $this->MultiCell($cell_width, 4, $debriefing, 0, 'L', false, 1, '', '', true, 0, true);
    }

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
