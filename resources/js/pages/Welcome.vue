<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Agreement = {
    id: number;
    title: string;
    kind: string;
    description: string;
};

type Citation = {
    id: number;
    agreement: string;
    page_label: string;
    snippet: string;
    score: number;
};

type SearchResult = {
    answer: string;
    citations: Citation[];
    answer_mode: string;
};

const props = defineProps<{
    agreements: Agreement[];
    llmAvailable: boolean;
}>();

const agreementList = ref([...props.agreements]);
const query = ref('');
const agreementId = ref('');
const result = ref<SearchResult | null>(null);
const isSearching = ref(false);
const error = ref('');
const showPasteForm = ref(false);
const pastedTitle = ref('');
const pastedBody = ref('');
const isSavingAgreement = ref(false);
const pasteError = ref('');

const selectedAgreement = computed(() =>
    agreementList.value.find(
        (agreement) => String(agreement.id) === agreementId.value,
    ),
);

const examples = [
    { label: 'Limitation of liability', icon: '◉' },
    { label: 'Termination rights', icon: '↗' },
    { label: 'Uptime commitment', icon: '⌁' },
];

function useExample(value: string) {
    query.value = value;
    void search();
}

async function search() {
    if (!query.value.trim() || isSearching.value) return;

    isSearching.value = true;
    error.value = '';
    result.value = null;

    try {
        const token = document.querySelector<HTMLMetaElement>(
            'meta[name="csrf-token"]',
        )?.content;
        const response = await fetch('/search', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token ?? '',
            },
            body: JSON.stringify({
                query: query.value,
                agreement_id: agreementId.value || null,
            }),
        });

        const payload = await response.json();

        if (!response.ok) {
            error.value =
                payload.message ??
                'Search could not be completed. Please try again.';
            return;
        }

        result.value = payload;
    } catch {
        error.value = 'We could not reach the clause finder. Please try again.';
    } finally {
        isSearching.value = false;
    }
}

async function saveAgreement() {
    if (isSavingAgreement.value) return;

    isSavingAgreement.value = true;
    pasteError.value = '';

    try {
        const token = document.querySelector<HTMLMetaElement>(
            'meta[name="csrf-token"]',
        )?.content;
        const response = await fetch('/agreements', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token ?? '',
            },
            body: JSON.stringify({
                title: pastedTitle.value,
                body: pastedBody.value,
            }),
        });
        const payload = await response.json();

        if (!response.ok) {
            pasteError.value =
                Object.values(payload.errors ?? {}).flat()[0] ??
                payload.message ??
                'The agreement could not be saved.';
            return;
        }

        agreementList.value.push(payload.agreement as Agreement);
        agreementId.value = String(payload.agreement.id);
        pastedTitle.value = '';
        pastedBody.value = '';
        showPasteForm.value = false;
    } catch {
        pasteError.value =
            'We could not save this agreement. Please try again.';
    } finally {
        isSavingAgreement.value = false;
    }
}

function percentage(score: number) {
    return `${Math.round(score * 100)}%`;
}
</script>

<template>
    <Head title="Clause Finder">
        <meta
            name="description"
            content="Search sample agreements and trace every answer back to its source clause."
        />
    </Head>

    <div class="min-h-screen bg-[#f5f4f0] text-[#17231f]">
        <header class="border-b border-[#e4e3dc] bg-[#faf9f6]">
            <div
                class="mx-auto flex max-w-[1440px] items-center justify-between px-5 py-4 sm:px-8 lg:px-12"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                    aria-label="Clause Finder home"
                >
                    <span
                        class="brand-mark flex h-10 w-10 items-center justify-center rounded-xl bg-[#1e3d33] text-[#f4b777]"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="h-5 w-5"
                            aria-hidden="true"
                        >
                            <path
                                d="M6.5 3.75h7l4 4v12.5h-11V3.75Z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M13.5 3.75v4h4M9.5 12h5M9.5 15.5h5"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                    <span>
                        <span
                            class="block text-[15px] font-semibold tracking-[-0.03em]"
                            >Clause Finder</span
                        >
                        <span
                            class="block text-[10px] font-semibold tracking-[0.17em] text-[#828b83] uppercase"
                            >Contract intelligence</span
                        >
                    </span>
                </a>

                <div
                    class="flex items-center gap-2 rounded-full border border-[#e2e6dd] bg-white px-3 py-2 text-[11px] font-medium text-[#53685d]"
                >
                    <span class="relative flex h-2 w-2">
                        <span
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#7bab77] opacity-50"
                        ></span>
                        <span
                            class="relative inline-flex h-2 w-2 rounded-full bg-[#719c6d]"
                        ></span>
                    </span>
                    <span>{{
                        llmAvailable
                            ? 'Gemini connected'
                            : 'Local retrieval ready'
                    }}</span>
                </div>
            </div>
        </header>

        <main
            class="mx-auto max-w-[1440px] px-5 pt-10 pb-16 sm:px-8 sm:pt-14 lg:px-12"
        >
            <div class="mb-9 flex flex-wrap items-end justify-between gap-5">
                <div>
                    <div
                        class="mb-3 flex items-center gap-2 text-[11px] font-semibold tracking-[0.19em] text-[#7c877e] uppercase"
                    >
                        <span class="h-px w-7 bg-[#d49b69]"></span>
                        Harbor Desk · sample workspace
                    </div>
                    <h1
                        class="max-w-3xl text-[36px] leading-[1.08] font-medium tracking-[-0.055em] text-[#1b3028] sm:text-[48px]"
                    >
                        Find the clause.<br class="hidden sm:block" />
                        Keep the context.
                    </h1>
                </div>
                <div class="hidden items-center gap-3 pb-1 sm:flex">
                    <div class="text-right">
                        <p class="text-[11px] font-semibold text-[#34473d]">
                            3 sample agreements
                        </p>
                        <p class="mt-0.5 text-[10px] text-[#8b9189]">
                            Fictional · English · searchable
                        </p>
                    </div>
                    <div class="flex -space-x-2">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#f5f4f0] bg-[#dfe8de] text-[10px] font-semibold text-[#49644e]"
                            >MS</span
                        >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#f5f4f0] bg-[#f0e3d6] text-[10px] font-semibold text-[#9b6c44]"
                            >ND</span
                        >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#f5f4f0] bg-[#dfe5ee] text-[10px] font-semibold text-[#526782]"
                            >SL</span
                        >
                    </div>
                </div>
            </div>

            <div
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px] xl:gap-8"
            >
                <section
                    class="overflow-hidden rounded-2xl border border-[#e4e3dc] bg-[#fffefa] shadow-[0_8px_30px_rgba(40,48,40,0.035)]"
                >
                    <div class="border-b border-[#eeede7] px-6 py-5 sm:px-8">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p
                                    class="text-[13px] font-semibold text-[#34443b]"
                                >
                                    Search your agreements
                                </p>
                                <p class="mt-1 text-[11px] text-[#8b9189]">
                                    Ask in plain English. Every result links to
                                    a source passage.
                                </p>
                            </div>
                            <span
                                class="hidden rounded-md bg-[#f2f3ed] px-2 py-1 font-mono text-[10px] text-[#879087] sm:block"
                                >RAG / v1.0</span
                            >
                        </div>
                    </div>

                    <div class="px-6 pt-6 sm:px-8">
                        <label
                            for="agreement"
                            class="mb-2 block text-[10px] font-semibold tracking-[0.14em] text-[#79847b] uppercase"
                            >Search scope</label
                        >
                        <div class="relative">
                            <select
                                id="agreement"
                                v-model="agreementId"
                                class="w-full appearance-none rounded-lg border border-[#e4e5dd] bg-white px-3.5 py-3 pr-10 text-[12px] text-[#39473f] transition outline-none focus:border-[#718a71] focus:ring-2 focus:ring-[#718a71]/10"
                            >
                                <option value="">All agreements</option>
                                <option
                                    v-for="agreement in agreementList"
                                    :key="agreement.id"
                                    :value="String(agreement.id)"
                                >
                                    {{ agreement.title }}
                                </option>
                            </select>
                            <svg
                                class="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-[#8b948d]"
                                viewBox="0 0 20 20"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="m5 7.5 5 5 5-5"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <form class="mt-4" @submit.prevent="search">
                            <label for="query" class="sr-only"
                                >What clause are you looking for?</label
                            >
                            <div
                                class="search-box rounded-xl border border-[#dfe3d9] bg-white p-2 transition focus-within:border-[#79907d] focus-within:ring-4 focus-within:ring-[#79907d]/10"
                            >
                                <div
                                    class="flex items-start gap-3 px-2.5 pt-2.5"
                                >
                                    <svg
                                        class="mt-0.5 h-[18px] w-[18px] shrink-0 text-[#9a7d5c]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="10.8"
                                            cy="10.8"
                                            r="6.8"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                        />
                                        <path
                                            d="m16 16 4.5 4.5"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                    <textarea
                                        id="query"
                                        v-model="query"
                                        rows="2"
                                        maxlength="200"
                                        placeholder="e.g. What happens if a party breaches the agreement?"
                                        class="min-h-[55px] w-full resize-none border-0 bg-transparent p-0 text-[14px] leading-6 text-[#2d3d34] outline-none placeholder:text-[#a6aaa2] focus:ring-0"
                                    ></textarea>
                                </div>
                                <div
                                    class="mt-2 flex items-center justify-between border-t border-[#f0f0ea] px-1 pt-2"
                                >
                                    <span
                                        class="px-2 text-[10px] text-[#a0a49c]"
                                        >{{ query.length }}/200</span
                                    >
                                    <button
                                        type="submit"
                                        :disabled="isSearching || !query.trim()"
                                        class="inline-flex items-center gap-2 rounded-lg bg-[#244638] px-4 py-2.5 text-[11px] font-semibold text-white transition hover:bg-[#19382b] disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <svg
                                            v-if="isSearching"
                                            class="h-3.5 w-3.5 animate-spin"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="9"
                                                stroke="currentColor"
                                                stroke-opacity=".25"
                                                stroke-width="3"
                                            />
                                            <path
                                                d="M21 12a9 9 0 0 0-9-9"
                                                stroke="currentColor"
                                                stroke-width="3"
                                                stroke-linecap="round"
                                            />
                                        </svg>
                                        <span>{{
                                            isSearching
                                                ? 'Searching…'
                                                : 'Find clauses'
                                        }}</span>
                                        <svg
                                            v-if="!isSearching"
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 16 16"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M3.25 8h9.5m-4-4 4 4-4 4"
                                                stroke="currentColor"
                                                stroke-width="1.4"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div
                            class="mt-4 flex flex-wrap items-center gap-2 pb-6"
                        >
                            <span
                                class="mr-1 text-[10px] font-medium text-[#929890]"
                                >Try</span
                            >
                            <button
                                v-for="example in examples"
                                :key="example.label"
                                type="button"
                                class="rounded-full border border-[#e7e7e0] bg-[#fafaf7] px-3 py-1.5 text-[10px] text-[#6f7a71] transition hover:border-[#c5d1c3] hover:bg-[#f1f5ef] hover:text-[#385844]"
                                @click="useExample(example.label)"
                            >
                                <span class="mr-1 text-[#b28b64]">{{
                                    example.icon
                                }}</span
                                >{{ example.label }}
                            </button>
                        </div>
                    </div>

                    <div
                        class="min-h-[260px] border-t border-[#eeede7] bg-[#fbfbf8] px-6 py-6 sm:px-8"
                    >
                        <div
                            v-if="error"
                            role="alert"
                            class="rounded-lg border border-[#ead3ca] bg-[#fff7f4] px-4 py-3 text-[12px] text-[#985b49]"
                        >
                            {{ error }}
                        </div>

                        <template v-else-if="result">
                            <div
                                class="mb-5 flex flex-wrap items-center justify-between gap-3"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-[#e4eee1] text-[#557c57]"
                                        >
                                            <svg
                                                class="h-3 w-3"
                                                viewBox="0 0 16 16"
                                                fill="none"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    d="m3.5 8.2 2.8 2.7 6.2-6"
                                                    stroke="currentColor"
                                                    stroke-width="1.6"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </span>
                                        <h2
                                            class="text-[13px] font-semibold text-[#33453b]"
                                        >
                                            {{ result.citations.length }} source
                                            passages found
                                        </h2>
                                    </div>
                                    <p
                                        class="mt-1 pl-7 text-[10px] text-[#969b93]"
                                    >
                                        Ranked by relevance to “{{ query }}”
                                    </p>
                                </div>
                                <span
                                    class="rounded-full border border-[#e5e8df] bg-white px-2.5 py-1 text-[9px] font-semibold tracking-[0.08em] text-[#7b887c] uppercase"
                                    >{{ result.answer_mode }}</span
                                >
                            </div>

                            <div
                                class="mb-5 rounded-xl border border-[#e6e8df] bg-white px-4 py-4 sm:px-5"
                            >
                                <p
                                    class="mb-2 text-[9px] font-semibold tracking-[0.16em] text-[#a07c58] uppercase"
                                >
                                    Quick read
                                </p>
                                <p
                                    class="text-[12px] leading-[1.75] text-[#56645a]"
                                >
                                    {{ result.answer }}
                                </p>
                                <p
                                    class="mt-3 border-t border-[#f0f0ea] pt-2.5 text-[9px] leading-4 text-[#9a9e95]"
                                >
                                    Research aid only. Review the full agreement
                                    and consult qualified counsel before making
                                    decisions.
                                </p>
                            </div>

                            <div
                                v-if="result.citations.length"
                                class="space-y-3"
                            >
                                <article
                                    v-for="(
                                        citation, index
                                    ) in result.citations"
                                    :key="citation.id"
                                    class="citation-card rounded-xl border border-[#e8e8e1] bg-white p-4 sm:p-5"
                                >
                                    <div
                                        class="mb-3 flex flex-wrap items-center justify-between gap-2"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="flex h-6 w-6 items-center justify-center rounded-md bg-[#f3eee6] text-[9px] font-semibold text-[#9b774f]"
                                                >{{
                                                    String(index + 1).padStart(
                                                        2,
                                                        '0',
                                                    )
                                                }}</span
                                            >
                                            <span
                                                class="text-[10px] font-semibold text-[#506054]"
                                                >{{ citation.agreement }}</span
                                            >
                                        </div>
                                        <div
                                            class="flex items-center gap-2 text-[9px] text-[#8b9288]"
                                        >
                                            <span
                                                class="rounded bg-[#f4f5f0] px-2 py-1"
                                                >{{ citation.page_label }}</span
                                            >
                                            <span
                                                class="font-mono font-medium text-[#718272]"
                                                >{{
                                                    percentage(citation.score)
                                                }}
                                                match</span
                                            >
                                        </div>
                                    </div>
                                    <blockquote
                                        class="border-l-2 border-[#cf9c6a] pl-3 text-[11px] leading-[1.8] text-[#647067]"
                                    >
                                        “{{ citation.snippet }}”
                                    </blockquote>
                                </article>
                            </div>

                            <div
                                v-else
                                class="rounded-xl border border-dashed border-[#dfe2d8] px-5 py-8 text-center"
                            >
                                <p
                                    class="text-[12px] font-medium text-[#59675d]"
                                >
                                    No close match in this sample set
                                </p>
                                <p class="mt-1 text-[10px] text-[#969b93]">
                                    Try a broader phrase or search all
                                    agreements.
                                </p>
                            </div>
                        </template>

                        <div
                            v-else-if="isSearching"
                            class="flex min-h-[200px] flex-col items-center justify-center text-center"
                        >
                            <div
                                class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-[#edf2e9] text-[#668163]"
                            >
                                <svg
                                    class="h-5 w-5 animate-pulse"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <circle
                                        cx="10.8"
                                        cy="10.8"
                                        r="6.8"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                    />
                                    <path
                                        d="m16 16 4.5 4.5"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>
                            <p class="text-[12px] font-semibold text-[#526155]">
                                Reading the fine print
                            </p>
                            <p class="mt-1 text-[10px] text-[#929a90]">
                                Chunking agreements · scoring passages ·
                                preparing citations
                            </p>
                        </div>

                        <div
                            v-else
                            class="flex min-h-[200px] flex-col items-center justify-center text-center"
                        >
                            <div
                                class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl border border-[#e5e7df] bg-white text-[#839184] shadow-sm"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M6.5 3.75h7l4 4v12.5h-11V3.75Z"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="M13.5 3.75v4h4M9.5 12h5M9.5 15.5h5"
                                        stroke="currentColor"
                                        stroke-width="1.4"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>
                            <p class="text-[12px] font-semibold text-[#526155]">
                                Your citations will appear here
                            </p>
                            <p
                                class="mt-1 max-w-[310px] text-[10px] leading-5 text-[#929a90]"
                            >
                                Ask about a contract term or choose an example
                                above. Results include the original passage and
                                a relevance score.
                            </p>
                        </div>
                    </div>
                </section>

                <aside class="space-y-5">
                    <section
                        class="rounded-2xl border border-[#e4e3dc] bg-[#fffefa] p-5 shadow-[0_8px_30px_rgba(40,48,40,0.025)] sm:p-6"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2
                                    class="text-[12px] font-semibold text-[#3d4c42]"
                                >
                                    Agreement library
                                </h2>
                                <p class="mt-1 text-[10px] text-[#969b93]">
                                    Fictional documents by Harbor Desk
                                </p>
                            </div>
                            <span
                                class="rounded-md bg-[#f1f3ed] px-2 py-1 text-[9px] font-semibold text-[#778577]"
                                >{{ agreementList.length }} DOCS</span
                            >
                        </div>

                        <button
                            type="button"
                            class="mb-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-[#d9ded5] bg-[#fafbf8] px-3 py-2.5 text-[10px] font-semibold text-[#637466] transition hover:border-[#afc1ae] hover:bg-[#f3f7f1]"
                            @click="showPasteForm = !showPasteForm"
                        >
                            <span
                                class="text-[14px] leading-none text-[#a17d58]"
                                >+</span
                            >
                            {{
                                showPasteForm
                                    ? 'Close paste form'
                                    : 'Paste agreement text'
                            }}
                        </button>

                        <form
                            v-if="showPasteForm"
                            class="mb-4 space-y-2 rounded-xl border border-[#e8e9e1] bg-[#fbfcf9] p-3"
                            @submit.prevent="saveAgreement"
                        >
                            <label
                                for="pasted-title"
                                class="block text-[9px] font-semibold tracking-[0.1em] text-[#79847b] uppercase"
                                >Document title</label
                            >
                            <input
                                id="pasted-title"
                                v-model="pastedTitle"
                                required
                                maxlength="120"
                                placeholder="e.g. Vendor services agreement"
                                class="w-full rounded-md border border-[#e4e5dd] bg-white px-2.5 py-2 text-[10px] text-[#39473f] outline-none focus:border-[#9caf9a]"
                            />
                            <label
                                for="pasted-body"
                                class="block pt-1 text-[9px] font-semibold tracking-[0.1em] text-[#79847b] uppercase"
                                >Agreement text</label
                            >
                            <textarea
                                id="pasted-body"
                                v-model="pastedBody"
                                required
                                minlength="100"
                                maxlength="50000"
                                rows="5"
                                placeholder="Paste at least 100 characters of plain text…"
                                class="w-full resize-y rounded-md border border-[#e4e5dd] bg-white px-2.5 py-2 text-[10px] leading-5 text-[#39473f] outline-none placeholder:text-[#a6aaa2] focus:border-[#9caf9a]"
                            ></textarea>
                            <p
                                v-if="pasteError"
                                role="alert"
                                class="text-[9px] leading-4 text-[#985b49]"
                            >
                                {{ pasteError }}
                            </p>
                            <div
                                class="flex items-center justify-between gap-2 pt-1"
                            >
                                <span
                                    class="text-[8px] leading-4 text-[#969b93]"
                                    >Stored in this local demo · max 50,000
                                    characters</span
                                >
                                <button
                                    type="submit"
                                    :disabled="
                                        isSavingAgreement ||
                                        pastedBody.length < 100 ||
                                        !pastedTitle.trim()
                                    "
                                    class="shrink-0 rounded-md bg-[#244638] px-3 py-2 text-[9px] font-semibold text-white transition hover:bg-[#19382b] disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        isSavingAgreement
                                            ? 'Saving…'
                                            : 'Add agreement'
                                    }}
                                </button>
                            </div>
                        </form>

                        <div class="space-y-2.5">
                            <button
                                v-for="agreement in agreementList"
                                :key="agreement.id"
                                type="button"
                                class="agreement-row group flex w-full items-start gap-3 rounded-xl border p-3 text-left transition"
                                :class="
                                    agreementId === String(agreement.id)
                                        ? 'border-[#b8c9b8] bg-[#f5f8f2]'
                                        : 'border-[#eeeee8] bg-white hover:border-[#d4ddd1] hover:bg-[#fbfcf9]'
                                "
                                @click="
                                    agreementId =
                                        agreementId === String(agreement.id)
                                            ? ''
                                            : String(agreement.id)
                                "
                            >
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[9px] font-bold tracking-[0.08em]"
                                    :class="
                                        agreement.kind === 'MSA'
                                            ? 'bg-[#e5ecdf] text-[#597253]'
                                            : agreement.kind === 'NDA'
                                              ? 'bg-[#f3e8dc] text-[#a2744c]'
                                              : 'bg-[#e5eaf1] text-[#59708b]'
                                    "
                                    >{{ agreement.kind }}</span
                                >
                                <span class="min-w-0 flex-1">
                                    <span
                                        class="block text-[10px] leading-4 font-semibold text-[#4a584e]"
                                        >{{ agreement.title }}</span
                                    >
                                    <span
                                        class="mt-1 block text-[9px] leading-[1.55] text-[#969c92]"
                                        >{{ agreement.description }}</span
                                    >
                                </span>
                                <svg
                                    class="mt-1 h-3.5 w-3.5 shrink-0 text-[#a2aa9f] transition group-hover:translate-x-0.5"
                                    viewBox="0 0 16 16"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M3.25 8h9.5m-4-4 4 4-4 4"
                                        stroke="currentColor"
                                        stroke-width="1.3"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </div>
                        <p
                            v-if="selectedAgreement"
                            class="mt-3 text-[9px] text-[#7c897e]"
                        >
                            Scoped to {{ selectedAgreement.kind }} · click again
                            to search all
                        </p>
                    </section>

                    <section
                        class="overflow-hidden rounded-2xl bg-[#1f3d32] p-5 text-[#e9eee7] sm:p-6"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="flex items-center gap-2 text-[10px] font-semibold tracking-[0.15em] text-[#c2d0c2] uppercase"
                            >
                                <span
                                    class="flex h-6 w-6 items-center justify-center rounded-md bg-white/10 text-[#e3b17e]"
                                    >✳</span
                                >
                                How it works
                            </div>
                            <span
                                class="rounded-full border border-white/15 px-2 py-1 text-[8px] tracking-[0.1em] text-[#c2d0c2] uppercase"
                                >Explainable AI</span
                            >
                        </div>
                        <div class="space-y-0">
                            <div class="flex gap-3 pb-4">
                                <span
                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/20 text-[8px] text-[#d4b28e]"
                                    >01</span
                                >
                                <div>
                                    <p class="text-[10px] font-semibold">
                                        Query
                                    </p>
                                    <p
                                        class="mt-0.5 text-[9px] leading-4 text-[#b6c5b8]"
                                    >
                                        Your question becomes a search vector.
                                    </p>
                                </div>
                            </div>
                            <div class="ml-2.5 h-3 w-px bg-white/15"></div>
                            <div class="flex gap-3 py-3">
                                <span
                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/20 text-[8px] text-[#d4b28e]"
                                    >02</span
                                >
                                <div>
                                    <p class="text-[10px] font-semibold">
                                        Chunk + score
                                    </p>
                                    <p
                                        class="mt-0.5 text-[9px] leading-4 text-[#b6c5b8]"
                                    >
                                        TF-IDF cosine ranks agreement passages.
                                    </p>
                                </div>
                            </div>
                            <div class="ml-2.5 h-3 w-px bg-white/15"></div>
                            <div class="flex gap-3 pt-4">
                                <span
                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/20 text-[8px] text-[#d4b28e]"
                                    >03</span
                                >
                                <div>
                                    <p class="text-[10px] font-semibold">
                                        Cite + explain
                                    </p>
                                    <p
                                        class="mt-0.5 text-[9px] leading-4 text-[#b6c5b8]"
                                    >
                                        Source passages ground the optional
                                        rewrite.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="mt-5 border-t border-white/10 pt-4 text-[9px] text-[#b6c5b8]"
                        >
                            <span
                                class="mr-1.5 inline-block h-1.5 w-1.5 rounded-full"
                                :class="
                                    llmAvailable
                                        ? 'bg-[#a5cb8c]'
                                        : 'bg-[#dfb17e]'
                                "
                            ></span>
                            {{
                                llmAvailable
                                    ? 'Gemini answer rewrite enabled'
                                    : 'Local mode · no API key required'
                            }}
                        </div>
                    </section>

                    <p class="px-1 text-[9px] leading-[1.7] text-[#989d94]">
                        Sample agreements are fictional product demos and are
                        not legal advice. Never upload confidential agreements
                        to this demo.
                    </p>
                </aside>
            </div>

            <footer
                class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-[#e3e3dc] pt-5 text-[9px] text-[#989d94]"
            >
                <span
                    >Clause Finder
                    <span class="mx-1.5 text-[#c4c6be]">·</span> A transparent
                    retrieval demo</span
                >
                <span>Built with Laravel, Vue &amp; TF-IDF</span>
            </footer>
        </main>
    </div>
</template>
