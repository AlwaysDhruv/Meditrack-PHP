<?php
require "../libs/fpdf/fpdf.php";

function generateRecordPDF($record, $doctor, $patientEmail) {

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont("Arial", "B", 18);

    // Header
    $pdf->Cell(0, 10, "MediTrack - Health Record", 0, 1, "C");
    $pdf->Ln(5);

    $pdf->SetFont("Arial", "", 12);

    $pdf->Cell(0, 10, "Doctor: " . $doctor["name"], 0, 1);
    $pdf->Cell(0, 10, "Patient Email: " . $patientEmail, 0, 1);
    $pdf->Cell(0, 10, "Date: " . date("d M Y, h:i A"), 0, 1);
    $pdf->Ln(5);

    $pdf->SetFont("Arial", "B", 14);
    $pdf->Cell(0, 10, "Record Details:", 0, 1);
    $pdf->Ln(3);

    $pdf->SetFont("Arial", "", 12);

    $pdf->MultiCell(0, 8, "Title: " . $record["title"]);
    $pdf->Ln(1);

    $pdf->MultiCell(0, 8, "Description: " . $record["description"]);
    $pdf->Ln(2);

    if ($record["bp"]) $pdf->MultiCell(0, 8, "Blood Pressure: " . $record["bp"]);
    if ($record["pulse"]) $pdf->MultiCell(0, 8, "Pulse: " . $record["pulse"]);
    if ($record["temperature"]) $pdf->MultiCell(0, 8, "Temperature: " . $record["temperature"]);

    $filename = "../uploads/records/record_" . time() . ".pdf";

    $pdf->Output("F", $filename);

    return $filename;
}
?>
