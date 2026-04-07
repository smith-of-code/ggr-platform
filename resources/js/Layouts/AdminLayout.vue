<template>
  <div class="flex min-h-screen bg-gray-50">
    <ToastNotifications />
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col border-r border-gray-200 bg-white">
      <!-- Logo -->
      <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-6">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#003274]">
          <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-bold text-gray-900">Росатом Travel</p>
          <p class="text-xs text-gray-400">Админ-панель</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <Link
          :href="route('admin.dashboard')"
          :class="[isActive('admin.dashboard') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.dashboard') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
          </svg>
          Дашборд
        </Link>

        <Link
          :href="route('admin.applications.index')"
          :class="[isActive('admin.applications') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.applications') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
          </svg>
          Заявки
        </Link>

        <div class="pb-1 pt-4">
          <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Контент портала</p>
        </div>

        <Link
          :href="route('admin.cities.index')"
          :class="[isActive('admin.cities') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.cities') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
          </svg>
          Города
        </Link>

        <Link
          :href="route('admin.tours.index')"
          :class="[isActive('admin.tours') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.tours') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
          </svg>
          Каталог туров
        </Link>

        <Link
          :href="route('admin.tour-reviews.index')"
          :class="[isActive('admin.tour-reviews') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.tour-reviews') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
          </svg>
          Отзывы
        </Link>

        <Link
          :href="route('admin.directions.index')"
          :class="[isActive('admin.directions') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.directions') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
          </svg>
          Направления
        </Link>

        <Link
          :href="route('admin.atoms-vkusa.edit')"
          :class="[isActive('admin.atoms-vkusa') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.atoms-vkusa') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
          </svg>
          Атомы вкуса
        </Link>

        <Link
          :href="route('admin.blog.index')"
          :class="[isActive('admin.blog') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.blog') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
          </svg>
          Блог
        </Link>

        <Link
          :href="route('admin.blog-subscribers.index')"
          :class="[isActive('admin.blog-subscribers') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.blog-subscribers') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
          </svg>
          Подписчики
        </Link>

        <Link
          :href="route('admin.education-products.index')"
          :class="[isActive('admin.education-products') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.education-products') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
          </svg>
          Продукты ВШГР
        </Link>

        <Link
          :href="route('admin.recipes.index')"
          :class="[isActive('admin.recipes') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.recipes') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
          </svg>
          Рецепты
        </Link>

        <div class="pb-1 pt-4">
          <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Сайт</p>
        </div>

        <Link
          :href="route('admin.opportunity-tours-page.index')"
          :class="[isActive('admin.opportunity-tours-page') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.opportunity-tours-page') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
          </svg>
          Туры возможностей
        </Link>

        <Link
          :href="route('admin.research-page.index')"
          :class="[isActive('admin.research-page') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.research-page') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          Исследования
        </Link>

        <Link
          :href="route('admin.timeline.index')"
          :class="[isActive('admin.timeline') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.timeline') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />
          </svg>
          Хронология
        </Link>

        <div class="pb-1 pt-4">
          <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">HR</p>
        </div>

        <Link
          :href="route('admin.vacancies.index')"
          :class="[isActive('admin.vacancies') ? 'bg-[#003274]/5 text-[#003274] font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150']"
        >
          <svg :class="[isActive('admin.vacancies') ? 'text-[#003274]' : 'text-gray-400 group-hover:text-gray-600']" class="h-5 w-5 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
          </svg>
          Вакансии
        </Link>

        <!-- LMS Switch -->
        <div class="mx-1 my-4 border-t border-gray-200"></div>
        <a
          :href="route('lms.admin.events.index')"
          class="group flex items-center gap-3 rounded-xl bg-gradient-to-r from-indigo-50 to-blue-50 px-3 py-3 text-sm font-semibold text-indigo-700 transition-all duration-150 hover:from-indigo-100 hover:to-blue-100 hover:shadow-sm"
        >
          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="leading-tight">Админка LMS ВШГР</p>
            <p class="text-xs font-normal text-indigo-500">Программы, тесты, участники</p>
          </div>
          <svg class="h-4 w-4 shrink-0 text-indigo-400 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
        </a>
      </nav>

      <!-- User footer -->
      <div class="border-t border-gray-100 p-4">
        <div class="flex items-center gap-3">
          <RAvatar :name="$page.props.auth?.user?.name || 'A'" size="sm" />
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-900">{{ $page.props.auth?.user?.name }}</p>
            <p class="truncate text-xs text-gray-400">{{ $page.props.auth?.user?.email }}</p>
          </div>
        </div>
        <div class="mt-3 flex gap-2">
          <Link :href="route('home')" class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 py-2 text-xs font-medium text-gray-600 transition hover:bg-gray-50">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
            На сайт
          </Link>
          <Link :href="route('logout')" method="post" as="button" class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 py-2 text-xs font-medium text-gray-600 transition hover:bg-red-50 hover:border-red-200 hover:text-red-600">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
            Выход
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <div class="ml-64 flex-1">
      <!-- Top bar -->
      <header class="sticky top-0 z-20 flex h-16 items-center border-b border-gray-200 bg-white/80 px-8 backdrop-blur-lg">
        <div class="flex-1" />
        <div v-if="$page.props.flash?.success" class="flex items-center gap-2 rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
          {{ $page.props.flash.success }}
        </div>
      </header>

      <main class="p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import ToastNotifications from '@/Components/ToastNotifications.vue'

function isActive(routePrefix) {
  const url = usePage().url
  if (routePrefix === 'admin.dashboard') return url === '/admin' || url === '/admin/'
  if (routePrefix === 'admin.applications') return url.startsWith('/admin/applications')
  if (routePrefix === 'admin.cities') return url.startsWith('/admin/cities')
  if (routePrefix === 'admin.tours') return url.startsWith('/admin/tours')
  if (routePrefix === 'admin.tour-reviews') return url.startsWith('/admin/tour-reviews')
  if (routePrefix === 'admin.blog') return url.startsWith('/admin/blog') && !url.startsWith('/admin/blog-subscribers')
  if (routePrefix === 'admin.blog-subscribers') return url.startsWith('/admin/blog-subscribers')
  if (routePrefix === 'admin.education-products') return url.startsWith('/admin/education-products')
  if (routePrefix === 'admin.recipes') return url.startsWith('/admin/recipes')
  if (routePrefix === 'admin.atoms-vkusa') return url.startsWith('/admin/atoms-vkusa')
  if (routePrefix === 'admin.vacancies') return url.startsWith('/admin/vacancies')
  if (routePrefix === 'admin.opportunity-tours-page') return url.startsWith('/admin/opportunity-tours-page')
  if (routePrefix === 'admin.research-page') return url.startsWith('/admin/research-page')
  if (routePrefix === 'admin.timeline') return url.startsWith('/admin/timeline')
  return false
}
</script>
