<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Required - {{ config('app.name', 'BeatWager') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center p-6 lg:p-8">
        <div class="max-w-md w-full">
            <div class="bg-white dark:bg-[#161615] rounded-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] p-8">
                <!-- Icon -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f53003] dark:bg-[#FF4433] rounded-full mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Login Required</h1>
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm">Please authenticate to continue</p>
                </div>

                <!-- Instructions -->
                <div class="space-y-4 text-[13px] leading-[20px]">
                    <div class="bg-[#FDFDFC] dark:bg-[#0a0a0a] rounded-sm p-4 border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <h2 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-3 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-[#f53003] dark:bg-[#FF4433] rounded-full text-xs font-medium text-white">1</span>
                            Open Telegram
                        </h2>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">
                            Find <strong class="text-[#1b1b18] dark:text-[#EDEDEC]">{{ '@' . $botName }}</strong> in your Telegram contacts or search for it.
                        </p>
                    </div>

                    <div class="bg-[#FDFDFC] dark:bg-[#0a0a0a] rounded-sm p-4 border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <h2 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-3 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-[#f53003] dark:bg-[#FF4433] rounded-full text-xs font-medium text-white">2</span>
                            Send Login Command
                        </h2>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">
                            Send the command <code class="bg-[#dbdbd7] dark:bg-[#3E3E3A] px-2 py-1 rounded-sm text-[#f53003] dark:text-[#FF4433] font-medium">/login</code> to the bot.
                        </p>
                    </div>

                    <div class="bg-[#FDFDFC] dark:bg-[#0a0a0a] rounded-sm p-4 border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <h2 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-3 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-[#f53003] dark:bg-[#FF4433] rounded-full text-xs font-medium text-white">3</span>
                            Click Your Login Link
                        </h2>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">
                            The bot will send you a personalized login link. Click it to authenticate.
                        </p>
                    </div>

                    <div class="bg-[#FDFDFC] dark:bg-[#0a0a0a] rounded-sm p-4 border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <h2 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-3 flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-[#f53003] dark:bg-[#FF4433] rounded-full text-xs font-medium text-white">4</span>
                            Return Here
                        </h2>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">
                            Once logged in, you can return to this page or bookmark it for later.
                        </p>
                    </div>
                </div>

                <!-- Help text -->
                <div class="mt-6 pt-6 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-[13px] text-center">
                        This authentication method keeps your account secure while making it easy to access from Telegram.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm">
                    Powered by <strong class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium">{{ config('app.name', 'BeatWager') }}</strong>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
