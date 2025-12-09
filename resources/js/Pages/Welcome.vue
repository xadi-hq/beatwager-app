<script setup lang="ts">
import LandingLayout from '@/Layouts/LandingLayout.vue';
</script>

<template>
    <LandingLayout
        title="WagerBot - Social Wagering for Telegram"
        description="Create friendly wagers, super challenges, and elimination tournaments with your friends. Track points, compete for bragging rights, and have fun in your Telegram groups!"
        og-image="/media/wagerbot-lg.png"
    >
        <!-- Hero Section -->
        <section id="hero" class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#5da7f8] via-[#6bb4ff] to-[#7ac1ff] dark:from-[#4a8bc9] dark:via-[#5498d8] dark:to-[#5ea5e7]">
            <div class="text-center px-4 sm:px-6 lg:px-8">
                <!-- Bot Avatar -->
                <div class="mb-8 flex justify-center">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/20 dark:bg-black/20 rounded-full blur-3xl"></div>
                        <img
                            src="/media/wagerbot-lg.png"
                            alt="WagerBot Avatar"
                            class="relative w-48 h-48 sm:w-64 sm:h-64 md:w-80 md:h-80 rounded-full shadow-2xl ring-8 ring-white/30 dark:ring-black/30"
                        />
                    </div>
                </div>

                <!-- Hero Text -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-6 drop-shadow-lg">
                    Welcome to WagerBot
                </h1>
                <p class="text-xl sm:text-2xl md:text-3xl text-white/90 mb-12 max-w-3xl mx-auto drop-shadow">
                    Your friendly social wagering companion for Telegram
                </p>

                <!-- CTA Button -->
                <a
                    href="https://t.me/WagerBot"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-3 px-8 py-4 text-lg sm:text-xl font-semibold text-[#5da7f8] bg-white rounded-full shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300"
                >
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.461-1.901-.903-1.056-.692-1.653-1.123-2.678-1.799-1.185-.781-.417-1.21.258-1.911.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.242-1.865-.442-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.14.121.099.155.232.171.326.016.093.036.306.02.472z"/>
                    </svg>
                    Start a chat with @WagerBot
                </a>

                <!-- Scroll Indicator -->
                <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                    <svg class="w-6 h-6 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="min-h-screen flex items-center justify-center bg-white dark:bg-neutral-900 px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl sm:text-5xl md:text-6xl font-bold text-neutral-900 dark:text-white mb-6">
                        What is this?
                    </h2>
                    <div class="w-24 h-1 bg-[#5da7f8] mx-auto rounded-full"></div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    <!-- Feature 1 -->
                    <div class="text-center p-8 rounded-3xl bg-gradient-to-br from-[#5da7f8]/10 to-[#7ac1ff]/10 dark:from-[#5da7f8]/20 dark:to-[#7ac1ff]/20 hover:shadow-xl transition-shadow duration-300">
                        <div class="text-5xl mb-4">🎲</div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">Social Wagers</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Create friendly wagers with your friends and track who owes who. All in your Telegram group chat!
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="text-center p-8 rounded-3xl bg-gradient-to-br from-[#5da7f8]/10 to-[#7ac1ff]/10 dark:from-[#5da7f8]/20 dark:to-[#7ac1ff]/20 hover:shadow-xl transition-shadow duration-300">
                        <div class="text-5xl mb-4">🏆</div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">Super Challenges</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Group-wide challenges where everyone contributes to the pot. Complete the task and claim your reward!
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="text-center p-8 rounded-3xl bg-gradient-to-br from-[#5da7f8]/10 to-[#7ac1ff]/10 dark:from-[#5da7f8]/20 dark:to-[#7ac1ff]/20 hover:shadow-xl transition-shadow duration-300">
                        <div class="text-5xl mb-4">🔥</div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">Elimination Challenges</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Tournament-style endurance challenges. Tap in, survive the longest, and take home the prize pool!
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="text-center p-8 rounded-3xl bg-gradient-to-br from-[#5da7f8]/10 to-[#7ac1ff]/10 dark:from-[#5da7f8]/20 dark:to-[#7ac1ff]/20 hover:shadow-xl transition-shadow duration-300">
                        <div class="text-5xl mb-4">📅</div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">Events</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Organize group events with attendance tracking. Reward participants with bonus points!
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="text-center p-8 rounded-3xl bg-gradient-to-br from-[#5da7f8]/10 to-[#7ac1ff]/10 dark:from-[#5da7f8]/20 dark:to-[#7ac1ff]/20 hover:shadow-xl transition-shadow duration-300">
                        <div class="text-5xl mb-4">🎯</div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">1-on-1 Challenges</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Challenge a friend directly. Offer points for tasks or earn points by completing them yourself.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="text-center p-8 rounded-3xl bg-gradient-to-br from-[#5da7f8]/10 to-[#7ac1ff]/10 dark:from-[#5da7f8]/20 dark:to-[#7ac1ff]/20 hover:shadow-xl transition-shadow duration-300">
                        <div class="text-5xl mb-4">🤖</div>
                        <h3 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">AI-Powered</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Smart conversations with your own tone of voice. WagerBot adapts to your group's personality.
                        </p>
                    </div>
                </div>

                <div class="text-center max-w-3xl mx-auto">
                    <p class="text-lg sm:text-xl text-neutral-600 dark:text-neutral-400 leading-relaxed mb-8">
                        WagerBot brings fun and engagement to your Telegram groups. Whether it's friendly bets with colleagues,
                        family challenges, or just keeping track of who owes what – WagerBot makes it easy and entertaining.
                    </p>
                    <p class="text-base sm:text-lg text-neutral-500 dark:text-neutral-500 italic">
                        Powered by AI, designed for fun, built for your friends.
                    </p>
                </div>
            </div>
        </section>

        <!-- Donate Section -->
        <section id="donate" class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#5da7f8]/5 via-white to-[#7ac1ff]/5 dark:from-[#5da7f8]/10 dark:via-neutral-900 dark:to-[#7ac1ff]/10 px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-4xl mx-auto text-center">
                <div class="mb-12">
                    <div class="text-6xl sm:text-7xl md:text-8xl mb-8 inline-block animate-pulse">☕</div>
                    <h2 class="text-4xl sm:text-5xl md:text-6xl font-bold text-neutral-900 dark:text-white mb-6">
                        Enjoying WagerBot?
                    </h2>
                    <div class="w-24 h-1 bg-[#5da7f8] mx-auto rounded-full mb-8"></div>
                </div>

                <p class="text-xl sm:text-2xl text-neutral-600 dark:text-neutral-400 mb-12 leading-relaxed max-w-2xl mx-auto">
                    WagerBot is free and always will be. But if you're loving it and want to support continued development,
                    I'd really appreciate a coffee! ❤️
                </p>

                <!-- Donate Button -->
                <a
                    href="https://buymeacoffee.com/xander.groesbeek"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-3 px-10 py-5 text-xl font-semibold text-white bg-gradient-to-r from-[#5da7f8] to-[#7ac1ff] rounded-full shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300"
                >
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.216 6.415l-.132-.666c-.119-.598-.388-1.163-1.001-1.379-.197-.069-.42-.098-.57-.241-.152-.143-.196-.366-.231-.572-.065-.378-.125-.756-.192-1.133-.057-.325-.102-.69-.25-.987-.195-.4-.597-.634-.996-.788a5.723 5.723 0 00-.626-.194c-1-.263-2.05-.36-3.077-.416a25.834 25.834 0 00-3.7.062c-.915.083-1.88.184-2.75.5-.318.116-.646.256-.888.501-.297.302-.393.77-.177 1.146.154.267.415.456.692.58.36.162.737.284 1.123.366 1.075.238 2.189.331 3.287.37 1.218.05 2.437.01 3.65-.118.299-.033.598-.073.896-.119.352-.054.578-.513.474-.834-.124-.383-.457-.531-.834-.473-.466.074-.96.108-1.382.146-1.177.08-2.358.082-3.536.006a22.228 22.228 0 01-1.157-.107c-.086-.01-.18-.025-.258-.036-.243-.036-.484-.08-.724-.13-.111-.027-.111-.185 0-.212h.005c.277-.06.557-.108.838-.147h.002c.131-.009.263-.032.394-.048a25.076 25.076 0 013.426-.12c.674.019 1.347.067 2.017.144l.228.031c.267.04.533.088.798.145.392.085.895.113 1.07.542.055.137.08.288.111.431l.319 1.484a.237.237 0 01-.199.284h-.003c-.037.006-.075.01-.112.015a36.704 36.704 0 01-4.743.295 37.059 37.059 0 01-4.699-.304c-.14-.017-.293-.042-.417-.06-.326-.048-.649-.108-.973-.161-.393-.065-.768-.032-1.123.161-.29.16-.527.404-.675.701-.154.316-.199.66-.267 1-.069.34-.176.707-.135 1.056.087.753.613 1.365 1.37 1.502a39.69 39.69 0 0011.343.376.483.483 0 01.535.53l-.071.697-1.018 9.907c-.041.41-.047.832-.125 1.237-.122.637-.553 1.028-1.182 1.171-.577.131-1.165.2-1.756.205-.656.004-1.31-.025-1.966-.022-.699.004-1.556-.06-2.095-.58-.475-.458-.54-1.174-.605-1.793l-.731-7.013-.322-3.094c-.037-.351-.286-.695-.678-.678-.336.015-.718.3-.678.679l.228 2.185.949 9.112c.147 1.344 1.174 2.068 2.446 2.272.742.12 1.503.144 2.257.156.966.016 1.942.053 2.892-.122 1.408-.258 2.465-1.198 2.616-2.657.34-3.332.683-6.663 1.024-9.995l.215-2.087a.484.484 0 01.39-.426c.402-.078.787-.212 1.074-.518.455-.488.546-1.124.385-1.766zm-1.478.772c-.145.137-.363.201-.578.233-2.416.359-4.866.54-7.308.46-1.748-.06-3.477-.254-5.207-.498-.17-.024-.353-.055-.47-.18-.22-.236-.111-.71-.054-.995.052-.26.152-.609.463-.646.484-.057 1.046.148 1.526.22.577.088 1.156.159 1.737.212 2.48.226 5.002.19 7.472-.14.45-.06.899-.13 1.345-.21.399-.072.84-.206 1.08.206.166.281.188.657.162.974a.544.544 0 01-.169.364zm-6.159 3.9c-.862.37-1.84.788-3.109.788a5.884 5.884 0 01-1.569-.217l.877 9.004c.065.78.717 1.38 1.5 1.38 0 0 1.243.065 1.658.065.447 0 1.786-.065 1.786-.065.783 0 1.434-.6 1.499-1.38l.94-9.95a3.996 3.996 0 00-1.322-.238c-.826 0-1.491.284-2.26.613z"/>
                    </svg>
                    Buy me a coffee
                </a>

                <p class="mt-8 text-sm text-neutral-500 dark:text-neutral-500">
                    Every contribution helps keep WagerBot running and getting better. Thank you! 🙏
                </p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-neutral-50 dark:bg-neutral-950 border-t border-neutral-200 dark:border-neutral-800 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-center text-neutral-600 dark:text-neutral-400 text-sm">
                        Made with ❤️ for Telegram communities
                    </p>
                    <a
                        href="/help"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-[#5da7f8] hover:text-[#4a8bc9] dark:text-[#7ac1ff] dark:hover:text-[#5da7f8] transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Help & Documentation
                    </a>
                </div>
            </div>
        </footer>
    </LandingLayout>
</template>
