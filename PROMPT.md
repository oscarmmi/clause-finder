Build Clause Finder in THIS folder: C:\xampp\htdocs\clause-finder

A recruiter-ready contract-clause finder: paste or pick a sample agreement, ask for a clause type, get cited snippets (RAG).

Laravel 13 + Vue 3 starter kit is already here if present. Reuse it. Do not create a second app inside NewPortfolio.

## Must satisfy

- Models: `Agreement` (title, body), `ClauseHit` (agreement_id, query, snippet, score, page_label).
- Chunk agreements, TF-IDF cosine retrieve top snippets for the query. Optional LLM rewrite of the answer with citations via `App\Services\Llm\LlmClient`.
- `FakeLlmClient` in tests and when no `GEMINI_API_KEY`. Optional Gemini when key is set. Timeout 12s. Never 500.
- Seed 3 fictional English agreements for "Harbor Desk" (MSA, NDA, SLA). No real clients, no PHI, no real company names from Oscar's work.
- UI: agreement picker, query box (examples: termination, limitation of liability, uptime), cited snippets with scores.
- Pest: "limitation of liability" returns at least one snippet from the MSA, guest POST 200, FakeLlm bound.
- README mermaid: Query → chunks → TF-IDF → citations. How to run without API key.

## GitHub

- git config user.name "Oscar Munoz" / user.email "oscarxxi@gmail.com"
- Commit: feat: add clause finder
- Public https://github.com/oscarmmi/clause-finder
- gh repo edit --description "Find cited contract clauses with TF-IDF RAG over sample agreements (Laravel + Vue)." --add-topic laravel --add-topic vue --add-topic ai --add-topic rag --add-topic php
- Stop. Print the URL.

No Impeccable. No Redis. No real legal advice.
