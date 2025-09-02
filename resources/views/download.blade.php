<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Download Game Client') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-lg shadow-lg overflow-hidden">
            <div class="px-8 py-12 text-white text-center">
                <h1 class="text-4xl font-bold mb-4">Download Metin2</h1>
                <p class="text-xl text-blue-100 mb-8">Get ready for epic adventures in the world of Metin2</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="inline-flex items-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Download Full Client (2.5 GB)
                    </a>
                    <a href="#" class="inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/20 transition-all duration-200 border border-white/20">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Download Patcher Only
                    </a>
                </div>
            </div>
        </div>

        <!-- System Requirements -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">System Requirements</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Minimum Requirements -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                            Minimum Requirements
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Operating System:</span>
                                <span class="font-medium">Windows 7 / 8 / 10 / 11</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Processor:</span>
                                <span class="font-medium">Intel Core 2 Duo 2.0 GHz</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Memory:</span>
                                <span class="font-medium">2 GB RAM</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Graphics:</span>
                                <span class="font-medium">DirectX 9.0c compatible</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Storage:</span>
                                <span class="font-medium">3 GB available space</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Network:</span>
                                <span class="font-medium">Broadband connection</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recommended Requirements -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                            Recommended Requirements
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Operating System:</span>
                                <span class="font-medium">Windows 10 / 11 64-bit</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Processor:</span>
                                <span class="font-medium">Intel Core i3 3.0 GHz</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Memory:</span>
                                <span class="font-medium">4 GB RAM</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Graphics:</span>
                                <span class="font-medium">DirectX 11 compatible</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Storage:</span>
                                <span class="font-medium">5 GB available space (SSD)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Network:</span>
                                <span class="font-medium">Stable broadband connection</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installation Guide -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Installation Guide</h2>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <!-- Step 1 -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-semibold">
                                1
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Download the Client</h4>
                            <p class="text-gray-600">Click the download button above to get the full game client or patcher.</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-semibold">
                                2
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Extract the Files</h4>
                            <p class="text-gray-600">Extract the downloaded archive to your desired installation directory.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-semibold">
                                3
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Run as Administrator</h4>
                            <p class="text-gray-600">Right-click on the game executable and select "Run as Administrator".</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-semibold">
                                4
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Create Account & Play</h4>
                            <p class="text-gray-600">
                                @guest
                                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 underline">Create your account</a> 
                                    and start your adventure!
                                @else
                                    You're all set! Launch the game and start your adventure.
                                @endguest
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Section -->
        <div class="bg-gray-50 rounded-lg p-6">
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Need Help?</h3>
                <p class="text-gray-600 mb-6">
                    Having trouble with the installation? Our support team is here to help!
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                        </svg>
                        Join Discord
                    </a>
                    <a href="#" class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>