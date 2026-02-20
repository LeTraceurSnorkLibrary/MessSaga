<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import {Head, Link} from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="MessSaga — Ваш личный архив воспоминаний" />

    <div class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50">
        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-orange-100">
            <nav class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-orange-400 to-rose-400 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <span
                            class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-rose-600 bg-clip-text text-transparent">
                            MessSaga
                        </span>
                    </div>

                    <!-- Auth Links -->
                    <div v-if="canLogin" class="flex items-center space-x-4">
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="text-gray-700 hover:text-orange-600 font-medium transition-colors"
                        >
                            Войти
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-rose-500 text-white rounded-full font-medium hover:shadow-lg hover:scale-105 transition-all duration-200"
                        >
                            Начать бесплатно
                        </Link>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-6">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center space-y-8">
                    <!-- Badge -->
                    <div
                        class="inline-flex items-center space-x-2 px-4 py-2 bg-white/60 backdrop-blur-sm rounded-full border border-orange-200 shadow-sm">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-sm font-medium text-gray-700">Сохраните свои воспоминания навсегда</span>
                    </div>

                    <!-- Main Heading -->
                    <h1 class="text-5xl md:text-7xl font-bold leading-tight">
                        <span
                            class="bg-gradient-to-r from-orange-600 via-rose-600 to-amber-600 bg-clip-text text-transparent">
                            Ваш личный архив
                        </span>
                        <br>
                        <span class="text-gray-800">всех переписок</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-xl md:text-2xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                        Сохраните переписки из Telegram, WhatsApp, VK и других мессенджеров
                        в одном безопасном месте. Ищите, вспоминайте, храните навсегда.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-8 py-4 bg-gradient-to-r from-orange-500 to-rose-500 text-white text-lg font-semibold rounded-full hover:shadow-2xl hover:scale-105 transition-all duration-200 flex items-center space-x-2"
                        >
                            <span>Начать прямо сейчас</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </Link>
                        <a
                            class="px-8 py-4 bg-white text-gray-700 text-lg font-semibold rounded-full border-2 border-orange-200 hover:border-orange-300 hover:shadow-lg transition-all duration-200"
                            href="#how-it-works"
                        >
                            Как это работает?
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="flex flex-wrap items-center justify-center gap-6 pt-8 text-sm text-gray-600">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path clip-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                      fill-rule="evenodd" />
                            </svg>
                            <span>Бесплатно</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path clip-rule="evenodd"
                                      d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                      fill-rule="evenodd" />
                            </svg>
                            <span>Зашифровано</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                            </svg>
                            <span>Без рекламы</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Image / Mockup -->
                <div class="mt-16 relative">
                    <div class="absolute inset-0 bg-gradient-to-t from-amber-100/50 to-transparent blur-3xl"></div>
                    <div class="relative bg-white rounded-3xl shadow-2xl border border-orange-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-500 to-rose-500 p-3 flex items-center space-x-2">
                            <div class="flex space-x-1.5">
                                <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            </div>
                            <div class="flex-1 text-center text-white text-sm font-medium">MessSaga</div>
                        </div>
                        <div class="p-8 bg-gradient-to-br from-orange-50/50 to-rose-50/50">
                            <div class="grid grid-cols-3 gap-4">
                                <!-- Telegram card -->
                                <div
                                    class="bg-white rounded-xl p-4 shadow-md border border-blue-100 hover:scale-105 transition-transform">
                                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z" />
                                        </svg>
                                    </div>
                                    <div class="text-xs font-semibold text-gray-700">Telegram</div>
                                    <div class="text-xs text-gray-500 mt-1">543 сообщения</div>
                                </div>
                                <!-- WhatsApp card -->
                                <div
                                    class="bg-white rounded-xl p-4 shadow-md border border-green-100 hover:scale-105 transition-transform">
                                    <div
                                        class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                        </svg>
                                    </div>
                                    <div class="text-xs font-semibold text-gray-700">WhatsApp</div>
                                    <div class="text-xs text-gray-500 mt-1">129 сообщений</div>
                                </div>
                                <!-- VK card -->
                                <div
                                    class="bg-white rounded-xl p-4 shadow-md border border-indigo-100 hover:scale-105 transition-transform">
                                    <div
                                        class="w-12 h-12 bg-indigo-500 rounded-xl flex items-center justify-center mb-3">
                                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.019-1.302.574-1.494c.586-.189 1.336 1.26 2.131 1.818.605.423 1.064.33 1.064.33l2.137-.03s1.117-.07.587-.966c-.044-.073-.31-.664-1.597-1.88-1.347-1.272-1.166-1.066.456-3.264.988-1.338 1.382-2.154 1.259-2.503-.117-.332-.84-.245-.84-.245l-2.406.015s-.178-.025-.31.056c-.13.079-.213.263-.213.263s-.382 1.036-.89 1.918c-1.07 1.86-1.499 1.958-1.674 1.842-.409-.267-.307-1.075-.307-1.648 0-1.792.266-2.537-.519-2.732-.261-.065-.453-.108-1.12-.115-.857-.009-1.583.003-1.994.208-.274.137-.485.442-.356.459.159.022.52.099.711.365.247.342.238 1.11.238 1.11s.142 2.109-.331 2.372c-.325.18-.77-.188-1.726-1.872-.489-.862-.859-1.816-.859-1.816s-.071-.178-.198-.273c-.154-.115-.37-.152-.37-.152l-2.286.015s-.343.01-.469.162c-.112.135-.009.414-.009.414s1.797 4.282 3.832 6.436c1.87 1.979 3.986 1.848 3.986 1.848h.958z" />
                                        </svg>
                                    </div>
                                    <div class="text-xs font-semibold text-gray-700">ВКонтакте</div>
                                    <div class="text-xs text-gray-500 mt-1">87 сообщений</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-6 bg-white/50">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                        Почему <span class="bg-gradient-to-r from-orange-600 to-rose-600 bg-clip-text text-transparent">MessSaga</span>?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Сохраните важные моменты вашей цифровой жизни в безопасном месте
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div
                        class="group bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl p-8 border border-orange-100 hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-orange-400 to-rose-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Полная безопасность</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Все сообщения шифруются перед сохранением. Только вы имеете доступ к своим данным.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="group bg-gradient-to-br from-rose-50 to-pink-50 rounded-2xl p-8 border border-rose-100 hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-rose-400 to-pink-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Умный поиск</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Найдите любое сообщение за секунды. Поиск по тексту, дате, контактам и мессенджерам.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="group bg-gradient-to-br from-amber-50 to-yellow-50 rounded-2xl p-8 border border-amber-100 hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-amber-400 to-yellow-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">С медиафайлами</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Фото, видео, голосовые сообщения — всё сохраняется вместе с перепиской.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div
                        class="group bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 border border-blue-100 hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Простой импорт</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Экспортируйте чат из мессенджера и загрузите в MessSaga. Всё автоматически!
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div
                        class="group bg-gradient-to-br from-purple-50 to-violet-50 rounded-2xl p-8 border border-purple-100 hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-purple-400 to-violet-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Навсегда</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Ваши переписки не исчезнут, даже если аккаунт в мессенджере будет удалён.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div
                        class="group bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 border border-green-100 hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Бесплатно</h3>
                        <p class="text-gray-600 leading-relaxed">
                            MessSaga абсолютно бесплатен. Без рекламы, без платных подписок.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="py-20 px-6">
            <div class="container mx-auto max-w-6xl">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                        Как это работает?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Три простых шага до сохранения ваших воспоминаний
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 relative">
                    <!-- Connection line -->
                    <div
                        class="hidden md:block absolute top-16 left-0 right-0 h-0.5 bg-gradient-to-r from-orange-200 via-rose-200 to-amber-200"></div>

                    <!-- Step 1 -->
                    <div class="relative">
                        <div
                            class="bg-white rounded-2xl p-8 border-2 border-orange-200 shadow-lg hover:shadow-xl transition-shadow">
                            <div
                                class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-gradient-to-br from-orange-500 to-rose-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                1
                            </div>
                            <div class="pt-4">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-orange-100 to-rose-100 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Зарегистрируйтесь</h3>
                                <p class="text-gray-600 text-center">
                                    Создайте бесплатный аккаунт за 30 секунд. Только email и пароль.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative">
                        <div
                            class="bg-white rounded-2xl p-8 border-2 border-rose-200 shadow-lg hover:shadow-xl transition-shadow">
                            <div
                                class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                2
                            </div>
                            <div class="pt-4">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-rose-100 to-pink-100 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                                    <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Загрузите чаты</h3>
                                <p class="text-gray-600 text-center">
                                    Экспортируйте переписки из мессенджера и загрузите файл в MessSaga.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative">
                        <div
                            class="bg-white rounded-2xl p-8 border-2 border-amber-200 shadow-lg hover:shadow-xl transition-shadow">
                            <div
                                class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                3
                            </div>
                            <div class="pt-4">
                                <div
                                    class="w-16 h-16 bg-gradient-to-br from-amber-100 to-yellow-100 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-3 text-center">Наслаждайтесь!</h3>
                                <p class="text-gray-600 text-center">
                                    Ищите, читайте, вспоминайте. Ваши данные в безопасности навсегда.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Supported Messengers -->
        <section class="py-20 px-6 bg-gradient-to-br from-orange-50 to-amber-50">
            <div class="container mx-auto max-w-4xl text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    Поддерживаемые мессенджеры
                </h2>
                <p class="text-lg text-gray-600 mb-12">
                    Мы постоянно добавляем новые платформы
                </p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div
                        class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-shadow border border-blue-100">
                        <div class="w-16 h-16 bg-blue-500 rounded-2xl flex items-center justify-center mb-3 mx-auto">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z" />
                            </svg>
                        </div>
                        <div class="font-semibold text-gray-800">Telegram</div>
                    </div>

                    <div
                        class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-shadow border border-green-100">
                        <div class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center mb-3 mx-auto">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                            </svg>
                        </div>
                        <div class="font-semibold text-gray-800">WhatsApp</div>
                    </div>

                    <div
                        class="bg-white rounded-2xl p-6 shadow-md hover:shadow-lg transition-shadow border border-indigo-100">
                        <div class="w-16 h-16 bg-indigo-500 rounded-2xl flex items-center justify-center mb-3 mx-auto">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.019-1.302.574-1.494c.586-.189 1.336 1.26 2.131 1.818.605.423 1.064.33 1.064.33l2.137-.03s1.117-.07.587-.966c-.044-.073-.31-.664-1.597-1.88-1.347-1.272-1.166-1.066.456-3.264.988-1.338 1.382-2.154 1.259-2.503-.117-.332-.84-.245-.84-.245l-2.406.015s-.178-.025-.31.056c-.13.079-.213.263-.213.263s-.382 1.036-.89 1.918c-1.07 1.86-1.499 1.958-1.674 1.842-.409-.267-.307-1.075-.307-1.648 0-1.792.266-2.537-.519-2.732-.261-.065-.453-.108-1.12-.115-.857-.009-1.583.003-1.994.208-.274.137-.485.442-.356.459.159.022.52.099.711.365.247.342.238 1.11.238 1.11s.142 2.109-.331 2.372c-.325.18-.77-.188-1.726-1.872-.489-.862-.859-1.816-.859-1.816s-.071-.178-.198-.273c-.154-.115-.37-.152-.37-.152l-2.286.015s-.343.01-.469.162c-.112.135-.009.414-.009.414s1.797 4.282 3.832 6.436c1.87 1.979 3.986 1.848 3.986 1.848h.958z" />
                            </svg>
                        </div>
                        <div class="font-semibold text-gray-800">ВКонтакте</div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-md border border-purple-100 opacity-60">
                        <div class="w-16 h-16 bg-purple-300 rounded-2xl flex items-center justify-center mb-3 mx-auto">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <div class="font-semibold text-gray-600">Скоро...</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="py-24 px-6 bg-gradient-to-r from-orange-500 via-rose-500 to-amber-500">
            <div class="container mx-auto max-w-4xl text-center text-white">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">
                    Готовы сохранить свои воспоминания?
                </h2>
                <p class="text-xl md:text-2xl mb-10 opacity-90">
                    Присоединяйтесь к MessSaga и никогда не теряйте важные моменты
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-10 py-5 bg-white text-orange-600 text-lg font-bold rounded-full hover:bg-gray-50 hover:shadow-2xl hover:scale-105 transition-all duration-200"
                    >
                        Начать бесплатно
                    </Link>
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="px-10 py-5 bg-transparent border-2 border-white text-white text-lg font-bold rounded-full hover:bg-white/10 transition-all duration-200"
                    >
                        Уже есть аккаунт
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-12 px-6">
            <div class="container mx-auto max-w-6xl">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-2 mb-4 md:mb-0">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-orange-400 to-rose-400 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">MessSaga</span>
                    </div>

                    <div class="text-sm text-center md:text-left">
                        © 2026 MessSaga. Ваш личный архив воспоминаний.
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
