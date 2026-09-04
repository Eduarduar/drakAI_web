<?php

namespace App\Services\Documents;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Convierte un archivo subido (PDF, Word .docx o texto plano) en texto
 * plano para dárselo a Gemini. Se apoya en `mimes:pdf,docx,txt` (validado
 * antes de llegar acá) para confiar en la extensión del archivo.
 */
class DocumentTextExtractor
{
    public function extract(UploadedFile $file): string
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'pdf' => $this->fromPdf($file),
            'docx' => $this->fromDocx($file),
            'txt' => $this->fromTxt($file),
            default => throw new InvalidArgumentException('Formato de archivo no soportado.'),
        };
    }

    private function fromTxt(UploadedFile $file): string
    {
        return (string) file_get_contents($file->getRealPath());
    }

    private function fromPdf(UploadedFile $file): string
    {
        return (new PdfParser())->parseFile($file->getRealPath())->getText();
    }

    private function fromDocx(UploadedFile $file): string
    {
        $document = WordIOFactory::load($file->getRealPath(), 'Word2007');

        $text = [];

        foreach ($document->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text[] = $this->elementText($element);
            }
        }

        return implode("\n", array_filter($text, fn ($line) => $line !== ''));
    }

    private function elementText(mixed $element): string
    {
        if ($element instanceof Text) {
            return $element->getText();
        }

        if ($element instanceof TextRun || $element instanceof AbstractContainer) {
            $parts = [];
            foreach ($element->getElements() as $child) {
                $parts[] = $this->elementText($child);
            }

            return implode(' ', array_filter($parts, fn ($part) => $part !== ''));
        }

        return '';
    }
}
