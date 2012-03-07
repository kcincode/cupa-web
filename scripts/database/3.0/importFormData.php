<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$minuteTable = new Model_DbTable_Minute();

$data = array(
    array(
        'year' => '2010',
        'name' => 'waiver',
        'location' => 'forms/2010_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2010',
        'name' => 'youth_waiver',
        'location' => 'forms/2010_yuc_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'waiver',
        'location' => 'forms/2011_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'youth_waiver',
        'location' => 'forms/2011_yuc_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'release',
        'location' => 'forms/2011_medical_authorization.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2012',
        'name' => 'waiver',
        'location' => 'forms/2012_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2012',
        'name' => 'youth_waiver',
        'location' => 'forms/2012_youth_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2012',
        'name' => 'release',
        'location' => 'forms/2012_medical_authorization.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2010',
        'name' => 'primary_outreach',
        'location' => 'forms/2010_primary_outreach_1.1.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'primary_outreach',
        'location' => 'forms/2011_primary_outreach_1.2.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2010',
        'name' => 'ysl_hs_waiver',
        'location' => 'forms/2010_ysl_hs_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2010',
        'name' => 'ysl_jr_waiver',
        'location' => 'forms/2010_ysl_jr_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2010',
        'name' => 'yuc_tournament_a',
        'location' => 'forms/2010_yuc_tournament_a.docx',
        'type' => 'docx',
    ),
    array(
        'year' => '2010',
        'name' => 'yuc_tournament_b',
        'location' => 'forms/2010_yuc_tournament_b.docx',
        'type' => 'docx',
    ),
    array(
        'year' => '2010',
        'name' => 'yuc_tournament_c',
        'location' => 'forms/2010_yuc_tournament_c.docx',
        'type' => 'docx',
    ),
    array(
        'year' => '2011',
        'name' => 'chusl_summer_clinics',
        'location' => 'forms/2011_chusl_summer_clinics.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'cupa_youth_chaperon_release',
        'location' => 'forms/2011_cupa_youth_chaperon_release.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'u16_open_flyer',
        'location' => 'forms/2011_u16_open_flyer.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'u19_girls_flyer',
        'location' => 'forms/2011_u19_girls_flyer.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'u19_open_flyer',
        'location' => 'forms/2011_u19_open_flyer.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'yuc_tournament_a',
        'location' => 'forms/2011_yuc_tournament_a.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'yuc_tournament_b',
        'location' => 'forms/2011_yuc_tournament_b.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'yuc_tournament_c',
        'location' => 'forms/2011_yuc_tournament_c.pdf',
        'type' => 'pdf',
    ),
);

$totalForms = count($data);

if(DEBUG) {
    echo "    Importing `Form` data:\n";
} else {
    echo "    Importing $totalForms Forms:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalForms);
}

$i = 0;
foreach($data as $row) {
    $filesize = filesize(__DIR__ . '/' . $row['location']);
    $md5 = md5_file(__DIR__ . '/' . $row['location']);
    $fp = fopen(__DIR__ . '/' . $row['location'], 'r');
    if($fp) {

        if(DEBUG) {
            echo "        Importing form `{$row['year']}_{$row['name']}.{$row['type']}`...";
        } else {
            $progressBar->update($i);
        }

        $formTable = new Model_DbTable_Form();
        $form = $formTable->createRow();
        $form->year = $row['year'];
        $form->name = $row['name'];
        $form->data = addslashes(fread($fp, $filesize));
        $form->type = $row['type'];
        $form->size = $filesize;
        $form->md5 = $md5;
        $form->uploaded_at = date('Y-m-d H:i:s');
        $form->modified_at = date('Y-m-d H:i:s');
        $form->modified_by = 1;
        $form->save();
        fclose($fp);

        if(DEBUG) {
            echo "Done\n";
        }

        $i++;
    }
}

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalForms);
    echo "\n";
}
