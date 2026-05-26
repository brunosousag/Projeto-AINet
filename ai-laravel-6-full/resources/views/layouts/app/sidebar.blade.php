<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $cartCount = collect(session('cart', []))->sum('qty');
            $settingsRoute = auth()->check()
                ? (auth()->user()->can('edit-profile') ? route('profile.edit') : route('security.edit'))
                : route('login');
        @endphp

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :href="route('home')" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Store" class="grid">
                    <flux:sidebar.item icon="photo" :href="route('tshirt-images.index')" :current="request()->routeIs('home') || request()->routeIs('tshirt-images.*')" wire:navigate>
                        Catalog
                    </flux:sidebar.item>

                    <div class="relative">
                        @if ($cartCount > 0)
                            <div class="absolute left-6 top-0 z-10">
                                <p class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs text-white">
                                    {{ $cartCount }}
                                </p>
                            </div>
                        @endif
                        <flux:sidebar.item icon="shopping-cart" :href="route('cart.show')" :current="request()->routeIs('cart.*')" wire:navigate>
                            Cart
                        </flux:sidebar.item>
                    </div>

                    @auth
                        @can('customer')
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('orders.index')" :current="request()->routeIs('orders.*') || request()->routeIs('checkout.*')" wire:navigate>
                                My orders
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="cloud-arrow-up" :href="route('personal-tshirt-images.index')" :current="request()->routeIs('personal-tshirt-images.*')" wire:navigate>
                                My images
                            </flux:sidebar.item>
                        @endcan
                    @endauth
                </flux:sidebar.group>
            </flux:sidebar.nav>

            @canany(['admin', 'employee'])
                <flux:sidebar.nav>
                    <flux:sidebar.group heading="Management" class="grid">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            Dashboard
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('orders.index')" :current="request()->routeIs('orders.*')" wire:navigate>
                            Orders
                        </flux:sidebar.item>
                        @can('admin')
                            <flux:sidebar.item icon="tag" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>
                                Categories
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="photo" :href="route('catalog-images.index')" :current="request()->routeIs('catalog-images.*')" wire:navigate>
                                Catalog images
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="swatch" :href="route('colors.index')" :current="request()->routeIs('colors.*')" wire:navigate>
                                Colors
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="banknotes" :href="route('prices.edit')" :current="request()->routeIs('prices.*')" wire:navigate>
                                Prices
                            </flux:sidebar.item>
                            <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                                Users
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                </flux:sidebar.nav>
            @endcanany

            <flux:spacer />

            @auth
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @else
                <flux:sidebar.item icon="user" :href="route('login')" :current="request()->routeIs('login')" wire:navigate>
                    Login
                </flux:sidebar.item>
            @endauth
        </flux:sidebar>

        @auth
            <flux:header class="lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
                <flux:spacer />
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                        :avatar="auth()->user()->photo_url ? auth()->user()->photo_full_url : null"
                    />

                    <flux:menu>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                    :src="auth()->user()->photo_url ? auth()->user()->photo_full_url : null"
                                />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="$settingsRoute" icon="cog" wire:navigate>
                                {{ __('Settings') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer"
                                data-test="logout-button"
                            >
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>
        @else
            <flux:header class="lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
                <flux:spacer />
                <flux:sidebar.item position="top" align="end" icon="user" :href="route('login')" :current="request()->routeIs('login')" wire:navigate>
                    Login
                </flux:sidebar.item>
            </flux:header>
        @endauth

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
