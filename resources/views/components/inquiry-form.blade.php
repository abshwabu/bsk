<div
    x-data="inquiryForm()"
    class="relative w-full rounded-2xl bg-white p-6 sm:p-8 md:p-10 shadow-sm border border-slate-100 text-left"
>
    {{-- Non-JS Flash Success Message --}}
    @if (session('inquiry_success'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium">{{ session('inquiry_success') }}</p>
            </div>
        </div>
    @endif

    {{-- Non-JS Flash Error Message --}}
    @if (isset($errors) && $errors->any())
        <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-800">
            <p class="text-sm font-semibold">Please correct the following errors:</p>
            <ul class="mt-2 list-disc list-inside text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Alpine Inline Success Banner --}}
    <div
        x-cloak
        x-show="submitted"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-5 text-emerald-800"
    >
        <div class="flex items-start gap-3">
            <svg class="h-6 w-6 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h4 class="text-base font-semibold">Message Sent!</h4>
                <p class="mt-1 text-sm text-emerald-700" x-text="successMessage"></p>
                <button
                    type="button"
                    @click="submitted = false"
                    class="mt-3 inline-flex items-center text-xs font-semibold text-emerald-800 underline hover:text-emerald-950"
                >
                    Send another message &rarr;
                </button>
            </div>
        </div>
    </div>

    {{-- Alpine Inline Global Error Banner --}}
    <div
        x-cloak
        x-show="errorMessage"
        x-transition
        class="mb-6 rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-800"
    >
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 8.25h.008v.008H12v-.008z" />
            </svg>
            <p class="text-sm font-medium" x-text="errorMessage"></p>
        </div>
    </div>

    <form
        action="{{ route('inquiry.store') }}"
        method="POST"
        @submit.prevent="submit"
        x-show="!submitted"
        class="space-y-4"
    >
        @csrf

        {{-- Honeypot field (hidden from humans, trapped by spam bots) --}}
        <div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
            <label for="hp_website">Website</label>
            <input
                type="text"
                name="hp_website"
                id="hp_website"
                tabindex="-1"
                autocomplete="off"
                x-model="formData.hp_website"
                value=""
            >
        </div>

        {{-- Time-based signature token --}}
        <input
            type="hidden"
            name="_token_time"
            value="{{ encrypt(time()) }}"
            :value="formData._token_time"
        >

        {{-- Source URL --}}
        <input
            type="hidden"
            name="source_url"
            value="{{ url()->current() }}"
            :value="formData.source_url"
        >

        {{-- Name --}}
        <div>
            <label for="inquiry_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                Your Name <span class="text-rose-500">*</span>
            </label>
            <input
                type="text"
                name="name"
                id="inquiry_name"
                required
                x-model="formData.name"
                value="{{ old('name') }}"
                placeholder="Jane Doe"
                :class="errors.name ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-slate-300 focus:border-primary focus:ring-primary/20'"
                class="mt-1.5 block w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition"
            >
            <template x-if="errors.name">
                <p class="mt-1 text-xs text-rose-600" x-text="errors.name[0]"></p>
            </template>
        </div>

        {{-- Contact Info (Email & Phone) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="inquiry_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Email Address
                </label>
                <input
                    type="email"
                    name="email"
                    id="inquiry_email"
                    x-model="formData.email"
                    value="{{ old('email') }}"
                    placeholder="jane@example.com"
                    :class="errors.email ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-slate-300 focus:border-primary focus:ring-primary/20'"
                    class="mt-1.5 block w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition"
                >
                <template x-if="errors.email">
                    <p class="mt-1 text-xs text-rose-600" x-text="errors.email[0]"></p>
                </template>
            </div>

            <div>
                <label for="inquiry_phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Phone Number
                </label>
                <input
                    type="tel"
                    name="phone"
                    id="inquiry_phone"
                    x-model="formData.phone"
                    value="{{ old('phone') }}"
                    placeholder="+1 (555) 000-0000"
                    :class="errors.phone ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-slate-300 focus:border-primary focus:ring-primary/20'"
                    class="mt-1.5 block w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition"
                >
                <template x-if="errors.phone">
                    <p class="mt-1 text-xs text-rose-600" x-text="errors.phone[0]"></p>
                </template>
            </div>
        </div>
        <p class="text-[11px] text-slate-400 -mt-2">
            Please provide at least one contact method (email or phone).
        </p>

        {{-- Subject --}}
        <div>
            <label for="inquiry_subject" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                Subject
            </label>
            <input
                type="text"
                name="subject"
                id="inquiry_subject"
                x-model="formData.subject"
                value="{{ old('subject') }}"
                placeholder="How can we help you?"
                :class="errors.subject ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-slate-300 focus:border-primary focus:ring-primary/20'"
                class="mt-1.5 block w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition"
            >
            <template x-if="errors.subject">
                <p class="mt-1 text-xs text-rose-600" x-text="errors.subject[0]"></p>
            </template>
        </div>

        {{-- Message --}}
        <div>
            <label for="inquiry_message" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                Message <span class="text-rose-500">*</span>
            </label>
            <textarea
                name="message"
                id="inquiry_message"
                rows="4"
                required
                x-model="formData.message"
                placeholder="Tell us about your project, inquiry, or question..."
                :class="errors.message ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-slate-300 focus:border-primary focus:ring-primary/20'"
                class="mt-1.5 block w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition resize-y"
            >{{ old('message') }}</textarea>
            <template x-if="errors.message">
                <p class="mt-1 text-xs text-rose-600" x-text="errors.message[0]"></p>
            </template>
        </div>

        {{-- Submit Button --}}
        <div class="pt-2">
            <button
                type="submit"
                :disabled="loading"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg
                    x-show="loading"
                    class="h-4 w-4 animate-spin text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Sending Message...' : 'Send Message'">Send Message</span>
            </button>
        </div>
    </form>
</div>

<script>
    function inquiryForm() {
        return {
            formData: {
                name: @json(old('name', '')),
                email: @json(old('email', '')),
                phone: @json(old('phone', '')),
                subject: @json(old('subject', '')),
                message: @json(old('message', '')),
                hp_website: '',
                _token_time: @json(encrypt(time())),
                source_url: typeof window !== 'undefined' ? window.location.href : @json(url()->current()),
            },
            loading: false,
            submitted: false,
            successMessage: '',
            errorMessage: '',
            errors: {},
            submit() {
                this.loading = true;
                this.errorMessage = '';
                this.errors = {};

                const token = document.querySelector('input[name="_token"]')?.value || '';

                fetch(@json(route('inquiry.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(this.formData)
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.errors || {};
                            this.errorMessage = data.message || 'Please check the form for errors.';
                        } else if (response.status === 429) {
                            this.errorMessage = 'Too many requests. Please wait before submitting another message.';
                        } else {
                            this.errorMessage = data.message || 'Something went wrong. Please try again.';
                        }
                        return;
                    }

                    this.submitted = true;
                    this.successMessage = data.message || 'Thank you! Your message has been sent successfully.';
                    this.formData.name = '';
                    this.formData.email = '';
                    this.formData.phone = '';
                    this.formData.subject = '';
                    this.formData.message = '';
                    this.formData.hp_website = '';
                })
                .catch(err => {
                    this.errorMessage = 'Network error. Please check your internet connection and try again.';
                })
                .finally(() => {
                    this.loading = false;
                });
            }
        };
    }
</script>
