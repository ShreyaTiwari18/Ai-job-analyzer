<?php

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;

class ResumeParserException extends Exception {}

class ResumeParser
{
    public static function extractText(string $filePath, string $fileType): string
    {
        return match ($fileType) {
            'pdf' => self::extractFromPdf($filePath),
            'docx' => self::extractFromDocx($filePath),
            default => throw new ResumeParserException("Unsupported file type: $fileType"),
        };
    }

    private static function extractFromPdf(string $filePath): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $text = trim($pdf->getText());

        if ($text === '') {
            throw new ResumeParserException('Could not extract any text from this PDF. It may be a scanned image without a text layer.');
        }

        return $text;
    }

    private static function extractFromDocx(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath, 'Word2007');
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= self::elementToText($element) . "\n";
            }
        }

        $text = trim($text);

        if ($text === '') {
            throw new ResumeParserException('Could not extract any text from this DOCX file.');
        }

        return $text;
    }

    private static function elementToText($element): string
    {
        if (method_exists($element, 'getText')) {
            $value = $element->getText();
            return is_string($value) ? $value : '';
        }

        if (method_exists($element, 'getElements')) {
            $text = '';
            foreach ($element->getElements() as $child) {
                $text .= self::elementToText($child) . ' ';
            }
            return $text;
        }

        return '';
    }
}
