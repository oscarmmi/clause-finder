<?php

namespace App\Services\Agreements;

use App\Models\Agreement;

class AgreementChunker
{
    private const MAX_CHUNK_LENGTH = 680;

    /**
     * Turn an agreement into section-aware passages suitable for retrieval.
     *
     * @return list<array{agreement_id: int, agreement: string, page_label: string, snippet: string}>
     */
    public function chunk(Agreement $agreement): array
    {
        $passages = [];
        $section = 'Agreement text';
        $buffer = '';

        foreach (preg_split('/\R{2,}/u', trim($agreement->body)) ?: [] as $paragraph) {
            $paragraph = trim(preg_replace('/\s+/u', ' ', $paragraph) ?? '');

            if ($paragraph === '') {
                continue;
            }

            if (preg_match('/^#{0,6}\s*(\d+(?:\.\d+)*)(?:[.)])?\s+(.+)$/u', $paragraph, $matches) === 1) {
                if ($buffer !== '') {
                    $passages[] = $this->passage($agreement, $section, $buffer);
                    $buffer = '';
                }

                $section = '§ '.$matches[1].' · '.trim($matches[2]);

                continue;
            }

            foreach ($this->splitLongParagraph($paragraph) as $piece) {
                $candidate = trim($buffer.' '.$piece);

                if ($buffer !== '' && mb_strlen($candidate) > self::MAX_CHUNK_LENGTH) {
                    $passages[] = $this->passage($agreement, $section, $buffer);
                    $buffer = $piece;
                } else {
                    $buffer = $candidate;
                }
            }
        }

        if ($buffer !== '') {
            $passages[] = $this->passage($agreement, $section, $buffer);
        }

        return $passages;
    }

    /** @return list<string> */
    private function splitLongParagraph(string $paragraph): array
    {
        if (mb_strlen($paragraph) <= self::MAX_CHUNK_LENGTH) {
            return [$paragraph];
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $paragraph) ?: [$paragraph];
        $pieces = [];
        $buffer = '';

        foreach ($sentences as $sentence) {
            if ($buffer !== '' && mb_strlen($buffer.' '.$sentence) > self::MAX_CHUNK_LENGTH) {
                $pieces[] = $buffer;
                $buffer = $sentence;
            } else {
                $buffer = trim($buffer.' '.$sentence);
            }
        }

        if ($buffer !== '') {
            $pieces[] = $buffer;
        }

        return $pieces;
    }

    /** @return array{agreement_id: int, agreement: string, page_label: string, snippet: string} */
    private function passage(Agreement $agreement, string $section, string $snippet): array
    {
        return [
            'agreement_id' => $agreement->id,
            'agreement' => $agreement->title,
            'page_label' => $section,
            'snippet' => trim($snippet),
        ];
    }
}
