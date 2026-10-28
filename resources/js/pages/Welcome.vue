<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type LibraryDocument = {
    id: number;
    title: string;
    filename: string;
    chunks: number;
};

type Citation = {
    filename: string;
    title: string;
    excerpt: string;
    position: number;
};

type QaResult = {
    id: number;
    question: string;
    answer: string;
    citations: Citation[];
    retrievedCount: number;
    usedFallback: boolean;
};

const props = defineProps<{
    documents: LibraryDocument[];
    examples: string[];
    llmMode: 'gemini' | 'demo';
    qaResult?: QaResult | null;
}>();

const form = useForm<{ question: string; documentId: number | null }>({
    question: '',
    documentId: null,
});
const search = ref('');
const result = ref<QaResult | null>(props.qaResult ?? null);
const visibleDocuments = computed(() =>
    props.documents.filter((document) =>
        `${document.title} ${document.filename}`
            .toLowerCase()
            .includes(search.value.toLowerCase()),
    ),
);
const selectedDocument = computed(() =>
    props.documents.find((document) => document.id === form.documentId),
);

function selectExample(question: string) {
    form.question = question;
    ask();
}

function ask() {
    if (!form.question.trim() || form.processing) return;

    form.post('/ask', {
        preserveScroll: true,
        onSuccess: (page) => {
            result.value = (page.props.qaResult as QaResult | null) ?? null;
        },
    });
}

function clearAnswer() {
    result.value = null;
    form.question = '';
    form.clearErrors();
}
</script>

<template>
    <Head title="Document Q&A" />

    <div class="desk-shell">
        <aside class="library-rail">
            <a class="brand-lockup" href="/" aria-label="Northwind Desk home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M4 7.2 12 3l8 4.2v9.6L12 21l-8-4.2V7.2Z"
                            stroke="currentColor"
                            stroke-width="1.7"
                        />
                        <path
                            d="m8 9.2 4 2.1 4-2.1M12 11.5v4.2"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </span>
                <span class="brand-name">northwind<span>desk</span></span>
                <span class="workspace-badge">AI</span>
            </a>

            <div class="rail-section-heading">
                <span>YOUR LIBRARY</span>
                <span class="document-total">{{ documents.length }}</span>
            </div>

            <label class="library-search">
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <circle
                        cx="8.8"
                        cy="8.8"
                        r="5.8"
                        stroke="currentColor"
                        stroke-width="1.5"
                    />
                    <path
                        d="m13.2 13.2 4 4"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                    />
                </svg>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Find a document"
                    aria-label="Find a document"
                />
                <kbd>/</kbd>
            </label>

            <div class="document-list" aria-label="Document library">
                <button
                    class="document-row"
                    :class="{ 'is-selected': form.documentId === null }"
                    type="button"
                    @click="form.documentId = null"
                >
                    <span class="document-icon all-docs-icon"
                        ><svg viewBox="0 0 20 20" fill="none">
                            <path
                                d="M4 5.5h12M4 10h12M4 14.5h12"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            /></svg
                    ></span>
                    <span class="document-meta"
                        ><strong>All documents</strong
                        ><small>Search your whole library</small></span
                    >
                    <span
                        v-if="form.documentId === null"
                        class="selected-dot"
                    />
                </button>
                <button
                    v-for="document in visibleDocuments"
                    :key="document.id"
                    class="document-row"
                    :class="{ 'is-selected': form.documentId === document.id }"
                    type="button"
                    @click="
                        form.documentId =
                            form.documentId === document.id ? null : document.id
                    "
                >
                    <span class="document-icon"
                        ><svg viewBox="0 0 20 20" fill="none">
                            <path
                                d="M5.3 2.8h6.2l3.2 3.3v10.8H5.3V2.8Z"
                                stroke="currentColor"
                                stroke-width="1.4"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M11.3 2.9v3.7h3.4M7.7 10h4.7M7.7 12.8h4.7"
                                stroke="currentColor"
                                stroke-width="1.3"
                                stroke-linecap="round"
                            /></svg
                    ></span>
                    <span class="document-meta"
                        ><strong>{{ document.title }}</strong
                        ><small>{{ document.chunks }} text chunks</small></span
                    >
                    <span
                        v-if="form.documentId === document.id"
                        class="selected-dot"
                    />
                </button>
                <p v-if="visibleDocuments.length === 0" class="empty-search">
                    No documents match that search.
                </p>
            </div>

            <div class="rail-spacer" />
            <div class="privacy-note">
                <span class="privacy-icon"
                    ><svg viewBox="0 0 20 20" fill="none">
                        <path
                            d="M10 2.8 16 5v4.7c0 3.5-2.4 5.9-6 7.5-3.6-1.6-6-4-6-7.5V5l6-2.2Z"
                            stroke="currentColor"
                            stroke-width="1.4"
                        />
                        <path
                            d="m7.7 9.8 1.5 1.5 3.2-3.3"
                            stroke="currentColor"
                            stroke-width="1.4"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        /></svg
                ></span>
                <span
                    ><strong>Your workspace is private</strong
                    ><small>Answers stay grounded in your library.</small></span
                >
            </div>
            <div class="profile-row">
                <div class="profile-avatar">N</div>
                <div class="profile-meta">
                    <strong>Northwind team</strong><small>Demo workspace</small>
                </div>
                <span class="profile-menu" aria-hidden="true">···</span>
            </div>
        </aside>

        <main class="desk-main">
            <header class="topbar">
                <div class="breadcrumb">
                    <span>Workspace</span><span class="crumb-separator">/</span
                    ><strong>Ask your library</strong>
                </div>
                <div class="topbar-actions">
                    <span class="model-indicator"
                        ><span class="model-dot" />{{
                            llmMode === 'gemini'
                                ? 'Gemini enabled'
                                : 'Demo mode'
                        }}</span
                    >
                    <button
                        class="help-button"
                        type="button"
                        aria-label="About this demo"
                        title="Answers use local TF-IDF retrieval and document citations."
                    >
                        ?
                    </button>
                </div>
            </header>

            <div class="desk-content">
                <section
                    class="qa-column"
                    aria-label="Ask your document library"
                >
                    <div class="welcome-block">
                        <div class="eyebrow">
                            <span class="sparkle">✳</span> YOUR KNOWLEDGE, IN
                            CONTEXT
                        </div>
                        <h1>
                            What would you like<br class="desktop-break" />
                            to know?
                        </h1>
                        <p>
                            Ask a question across your team's documents. Every
                            answer is grounded in a source you can open and
                            verify.
                        </p>
                    </div>

                    <form class="ask-card" @submit.prevent="ask">
                        <label class="sr-only" for="question"
                            >Ask a question about your documents</label
                        >
                        <textarea
                            id="question"
                            v-model="form.question"
                            rows="2"
                            maxlength="1000"
                            placeholder="Ask anything about your documents..."
                            @keydown.enter.exact.prevent="ask"
                        />
                        <div class="ask-card-footer">
                            <div class="ask-context">
                                <span class="context-sparkle">✳</span>
                                <template v-if="selectedDocument"
                                    >Searching
                                    <strong>{{ selectedDocument.title }}</strong
                                    ><button
                                        type="button"
                                        aria-label="Search all documents"
                                        @click="form.documentId = null"
                                    >
                                        ×
                                    </button></template
                                >
                                <template v-else
                                    >Searching across
                                    <strong
                                        >{{
                                            documents.length
                                        }}
                                        documents</strong
                                    ></template
                                >
                            </div>
                            <div class="ask-actions">
                                <span class="keyboard-hint">Enter ↵</span
                                ><button
                                    class="ask-button"
                                    type="submit"
                                    :disabled="
                                        form.processing || !form.question.trim()
                                    "
                                >
                                    <span
                                        v-if="form.processing"
                                        class="button-spinner"
                                    /><span v-else>Ask</span
                                    ><svg
                                        v-if="!form.processing"
                                        viewBox="0 0 20 20"
                                        fill="none"
                                    >
                                        <path
                                            d="M4 10h11m-4.5-4.5L15 10l-4.5 4.5"
                                            stroke="currentColor"
                                            stroke-width="1.6"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-if="form.errors.question" class="validation-error">
                            {{ form.errors.question }}
                        </p>
                    </form>

                    <div v-if="!result" class="examples-block">
                        <div class="examples-heading">
                            <span>TRY ASKING</span
                            ><span class="examples-line" />
                        </div>
                        <button
                            v-for="(example, index) in examples"
                            :key="example"
                            class="example-card"
                            type="button"
                            :disabled="form.processing"
                            @click="selectExample(example)"
                        >
                            <span class="example-number">0{{ index + 1 }}</span
                            ><span>{{ example }}</span
                            ><svg viewBox="0 0 20 20" fill="none">
                                <path
                                    d="M5 10h10m-4-4 4 4-4 4"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                        <div class="powered-note">
                            <span class="powered-star">✳</span> Answers are
                            generated from your docs, with citations included.
                        </div>
                    </div>

                    <section v-else class="answer-section" aria-live="polite">
                        <div class="answer-toolbar">
                            <div class="answer-label">
                                <span class="answer-mark">✳</span
                                ><span>ANSWER</span
                                ><span class="answer-source-count"
                                    >{{ result.retrievedCount }}
                                    {{
                                        result.retrievedCount === 1
                                            ? 'passage'
                                            : 'passages'
                                    }}
                                    found</span
                                >
                            </div>
                            <button
                                class="new-question"
                                type="button"
                                @click="clearAnswer"
                            >
                                New question <span>↗</span>
                            </button>
                        </div>
                        <div class="question-echo">
                            <span class="question-avatar">N</span
                            ><span>{{ result.question }}</span>
                        </div>
                        <article class="answer-card">
                            <div class="answer-card-header">
                                <span class="assistant-avatar">✳</span>
                                <div>
                                    <strong>Northwind assistant</strong
                                    ><small>{{
                                        result.usedFallback
                                            ? 'Retrieved context · model unavailable'
                                            : llmMode === 'gemini'
                                              ? 'Gemini · grounded in your library'
                                              : 'Context-grounded demo answer'
                                    }}</small>
                                </div>
                                <span class="answer-state"
                                    ><span /> GROUNDED</span
                                >
                            </div>
                            <p class="answer-copy">{{ result.answer }}</p>
                            <div
                                v-if="result.citations.length"
                                class="citations-block"
                            >
                                <div class="citation-heading">
                                    <span>Sources in your library</span
                                    ><span
                                        >{{
                                            result.citations.length
                                        }}
                                        cited</span
                                    >
                                </div>
                                <div class="citation-list">
                                    <article
                                        v-for="(
                                            citation, index
                                        ) in result.citations"
                                        :key="`${citation.filename}-${index}`"
                                        class="citation-card"
                                    >
                                        <div class="citation-file-icon">
                                            <svg
                                                viewBox="0 0 20 20"
                                                fill="none"
                                            >
                                                <path
                                                    d="M5 2.8h6l4 4v10.4H5V2.8Z"
                                                    stroke="currentColor"
                                                    stroke-width="1.4"
                                                    stroke-linejoin="round"
                                                />
                                                <path
                                                    d="M11 3v4h3.8M7.5 10.5h5M7.5 13h4"
                                                    stroke="currentColor"
                                                    stroke-width="1.2"
                                                    stroke-linecap="round"
                                                />
                                            </svg>
                                        </div>
                                        <div class="citation-content">
                                            <div class="citation-title-row">
                                                <strong>{{
                                                    citation.title
                                                }}</strong
                                                ><span
                                                    >PASSAGE
                                                    {{
                                                        String(
                                                            citation.position,
                                                        ).padStart(2, '0')
                                                    }}</span
                                                >
                                            </div>
                                            <div class="citation-filename">
                                                {{ citation.filename }}
                                            </div>
                                            <p>“{{ citation.excerpt }}”</p>
                                        </div>
                                    </article>
                                </div>
                            </div>
                            <div v-else class="no-sources">
                                <span>ⓘ</span> No matching passages were found,
                                so this answer does not cite a manual.
                            </div>
                        </article>
                        <p class="answer-disclaimer">
                            AI can make mistakes. Verify important details in
                            the cited source.
                        </p>
                    </section>
                </section>

                <aside
                    class="insight-column"
                    aria-label="About document search"
                >
                    <div class="insight-card retrieval-card">
                        <div class="insight-overline">
                            <span class="insight-icon retrieval-icon"
                                ><svg viewBox="0 0 20 20" fill="none">
                                    <circle
                                        cx="8.5"
                                        cy="8.5"
                                        r="5.2"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                    />
                                    <path
                                        d="m12.4 12.4 4 4M6.5 8.5h4M8.5 6.5v4"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                        stroke-linecap="round"
                                    /></svg></span
                            >HOW IT WORKS
                        </div>
                        <h2>Answers with receipts.</h2>
                        <p>
                            We find relevant passages first, then use them as
                            context for your answer.
                        </p>
                        <div class="pipeline-list">
                            <div>
                                <span class="pipeline-index">01</span
                                ><span
                                    ><strong>Find context</strong
                                    ><small
                                        >TF-IDF ranks relevant text</small
                                    ></span
                                >
                            </div>
                            <span class="pipeline-connector" />
                            <div>
                                <span class="pipeline-index">02</span
                                ><span
                                    ><strong>Write an answer</strong
                                    ><small
                                        >Based on retrieved passages</small
                                    ></span
                                >
                            </div>
                            <span class="pipeline-connector" />
                            <div>
                                <span class="pipeline-index">03</span
                                ><span
                                    ><strong>Show your sources</strong
                                    ><small
                                        >Every answer links to a manual</small
                                    ></span
                                >
                            </div>
                        </div>
                        <div class="retrieval-limit">
                            <span>RETRIEVAL LIMIT</span
                            ><strong>Top 5 passages</strong>
                        </div>
                    </div>
                    <div class="insight-card library-stat-card">
                        <div class="library-stat-icon">
                            <svg viewBox="0 0 20 20" fill="none">
                                <path
                                    d="M4.5 3h8l3 3v11h-11V3Z"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="M12.5 3v3.5H16"
                                    stroke="currentColor"
                                    stroke-width="1.4"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <div>
                            <strong>{{ documents.length }} documents</strong
                            ><span>Indexed and ready to search</span>
                        </div>
                        <span class="online-dot" />
                    </div>
                    <div class="demo-footnote">
                        <span class="demo-indicator" />{{
                            llmMode === 'gemini'
                                ? 'Connected to Gemini 2.5 Flash'
                                : 'Running with the built-in demo model'
                        }}<br /><span
                            >Library content is fictional sample data.</span
                        >
                    </div>
                </aside>
            </div>
            <footer class="main-footer">
                <span>© 2026 Northwind Desk</span
                ><span>Document Q&A <i /> Private workspace</span>
            </footer>
        </main>
    </div>
</template>
