Build PDF Q&A Studio in THIS folder: C:\xampp\htdocs\pdf-qa-studio

A recruiter-ready document Q&A desk. Seeded text "documents" (plain English manuals, not binary PDFs in v1) are chunked, retrieved, and answered with citations. Same RAG idea as cite-rag-desk, focused on a document library UI.

Laravel 13 + Vue 3 starter kit is already here if present. Reuse it. Do not create a second app inside NewPortfolio.

## Must satisfy

- Models: `LibraryDoc` (title, filename, body), `DocChunk` (library_doc_id, body, position), `QaTurn` (question, answer, retrieved_chunk_ids json).
- Ingest artisan `docs:ingest` rebuilds chunks (~500 chars, 50 overlap).
- Retrieve: pure-PHP TF-IDF cosine, top 5. No Redis, no vector DB.
- `App\Services\Llm\LlmClient` + `FakeLlmClient` when no `GEMINI_API_KEY`. Answers must list source filenames. Timeout 12s. Never unhandled 500.
- UI: left rail of documents, main ask box, answer + citation cards with filename. Three example questions.
- Seed 6 English ops manuals (onboarding, refunds, SLA, password reset, incident severity, data retention). Fictional "Northwind Desk" product. No real clients.
- Pest: retrieval hits the refunds doc for a refund question, citations include that filename, FakeLlm in tests, ingest command creates chunks.
- README mermaid: Library → Chunks → Retriever → LLM → Citations. How to run without API key.

## GitHub

- git config user.name "Oscar Munoz" / user.email "oscarxxi@gmail.com"
- Commit: feat: add pdf qa studio
- Public https://github.com/oscarmmi/pdf-qa-studio
- gh repo edit --description "Ask questions over a document library with RAG citations (Laravel + Vue)." --add-topic laravel --add-topic vue --add-topic rag --add-topic ai --add-topic php
- Stop. Print the URL.

No Impeccable. No Redis. No real PDF binary parsing in v1 (seeded text docs named like manuals.pdf in the UI).
