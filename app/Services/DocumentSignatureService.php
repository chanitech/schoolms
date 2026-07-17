<?php

namespace App\Services;

use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentSignatureService
{
    /**
     * Register an exported document and return its signature record.
     *
     * The content hash is an HMAC-SHA256 over the document's canonical
     * content (rows + metadata), keyed with the application key — it can
     * only be produced by this installation, so a matching code+hash on
     * the verification page proves the document came from the system
     * unaltered.
     */
    public function sign(string $docType, string $title, string $summary, array $rows): DocumentSignature
    {
        $code = $this->uniqueCode();

        $payload = json_encode([
            'type'    => $docType,
            'title'   => $title,
            'summary' => $summary,
            'rows'    => $rows,
            'code'    => $code,
        ]);

        return DocumentSignature::create([
            'code'         => $code,
            'doc_type'     => $docType,
            'title'        => $title,
            'summary'      => Str::limit($summary, 490),
            'content_hash' => hash_hmac('sha256', $payload, config('app.key')),
            'signed_by'    => Auth::id(),
        ]);
    }

    public function verify(string $code): ?DocumentSignature
    {
        // Public verification page has no tenant bound; codes are globally
        // unique so an unscoped lookup is safe.
        return DocumentSignature::withoutGlobalScopes()->with('signer')
            ->where('code', strtoupper(trim($code)))
            ->first();
    }

    private function uniqueCode(): string
    {
        do {
            // Unambiguous alphabet (no 0/O, 1/I/L) — easy to read off paper
            $code = 'DOC-' . strtoupper(substr(str_replace(
                ['0', 'O', '1', 'I', 'L'], ['2', '3', '4', '5', '6'],
                Str::random(12)
            ), 0, 10));
        } while (DocumentSignature::withoutGlobalScopes()->where('code', $code)->exists());

        return $code;
    }
}
