<div class="fi-topbar-mobile-hamburger ms-auto lg:hidden"
     x-data="{ open: false }"
     x-on:keydown.escape.window="open = false"
>
    <div class="hamburger">
        <button type="button"
                class="hamburger__button fi-topbar-mobile-hamburger__btn"
                x-on:click="open = true"
                aria-label="Открыть меню"
        >
            <svg class="hamburger__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </svg>
        </button>

        <template x-teleport="body">
            <div class="hamburger__portal"
                 x-cloak
                 x-bind:style="open ? 'pointer-events:auto' : 'pointer-events:none'"
            >
                <div class="hamburger__backdrop"
                     x-show="open"
                     x-on:click="open = false"
                     x-transition:enter="hamburger__backdrop-transition-active"
                     x-transition:enter-start="hamburger__backdrop-transition-enter-start"
                     x-transition:enter-end="hamburger__backdrop-transition-enter-end"
                     x-transition:leave="hamburger__backdrop-transition-active"
                     x-transition:leave-start="hamburger__backdrop-transition-leave-start"
                     x-transition:leave-end="hamburger__backdrop-transition-leave-end"
                ></div>
                <div class="hamburger__sheet"
                     role="dialog"
                     aria-modal="true"
                     x-show="open"
                     x-transition:enter="hamburger__sheet-transition-active"
                     x-transition:enter-start="hamburger__sheet-transition-enter-start"
                     x-transition:enter-end="hamburger__sheet-transition-enter-end"
                     x-transition:leave="hamburger__sheet-transition-active"
                     x-transition:leave-start="hamburger__sheet-transition-leave-start"
                     x-transition:leave-end="hamburger__sheet-transition-leave-end"
                >
                    <div class="hamburger__closer">
                        <button type="button"
                                class="hamburger__close-button"
                                x-on:click="open = false"
                                aria-label="Закрыть меню"
                        >
                            <svg class="hamburger__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"/>
                            </svg>
                        </button>
                    </div>

                    <div class="hamburger__body">
                        <div class="hamburger-panel">
                            <div class="hamburger-panel-links">
                                <a href="{{ url('/manage') }}"
                                   @class([
                                       'hamburger-panel-links__item',
                                       'hamburger-panel-links__item--active' => request()->is('manage*'),
                                   ])
                                   x-on:click="open = false"
                                >
                                    Админ-панель
                                </a>
                                <a href="{{ route('dashboard') }}"
                                   @class([
                                       'hamburger-panel-links__item',
                                       'hamburger-panel-links__item--active' => request()->routeIs('dashboard'),
                                   ])
                                   x-on:click="open = false"
                                >
                                    Переписки
                                </a>
                            </div>

                            <div class="hamburger-panel__user">
                                <div class="hamburger-panel-user">
                                    <a href="{{ route('profile.edit') }}" class="hamburger-panel-user__profile"
                                       x-on:click="open = false">
                                        <div class="hamburger-panel-user__label">К профилю:</div>
                                        <div class="hamburger-panel-user__name">{{ filament()->auth()->user()?->name }}</div>
                                        <div class="hamburger-panel-user__email">{{ filament()->auth()->user()?->email }}</div>
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="hamburger-panel-user__logout">
                                            <span class="hamburger-panel-user__logout-text">Выйти</span>
                                            <svg class="hamburger-panel-user__logout-icon" fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-7.5A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15M9 12h12m0 0l-3-3m3 3l-3 3"
                                                      stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="1.75"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
