<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const sections = ref([
  { id: 'home', label: 'Home' },
  { id: 'features', label: 'Features' },
  { id: 'clients', label: 'Clients' },
  { id: 'pricing', label: 'Pricing' },
  { id: 'api', label: 'API' },
]);

const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<template>
  <Head title="Welcome">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <!-- Fixed Header -->
  <header class="fixed top-0 left-0 right-0 bg-white z-50 shadow-sm">
    <div class="container mx-auto px-6 py-4">
      <div class="max-w-6xl mx-auto flex justify-between items-center">
        <div class="flex items-center">
          <div class="text-2xl font-bold text-green-500 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-4h2v2h-2zm0-10h2v8h-2z" />
            </svg>
            WABLAS<span class="text-sm align-top">v.2</span>
          </div>
        </div>
        <nav class="hidden md:flex space-x-6">
          <button
            v-for="section in sections"
            :key="section.id"
            @click="scrollToSection(section.id)"
            class="text-gray-700 hover:text-green-500 font-medium"
          >
            {{ section.label }}
          </button>
          <Link :href="route('login')" class="text-gray-700 hover:text-green-500 font-medium">
            Login
          </Link>
          <Link :href="route('register')" class="bg-green-500 text-white px-6 py-2 rounded-full hover:bg-green-600 transition-colors">
            Register
          </Link>
        </nav>
      </div>
    </div>
  </header>

  <!-- Main Content with Sections -->
  <main class="pt-10">
    <!-- Hero Section (Home) -->
    <section id="home" class="min-h-screen">
      <!-- First Part (Image 1) -->
      <div class="bg-gradient-to-b  pb-32 pt-16 relative overflow-hidden">
        <div class="container mx-auto px-6">
          <!-- Centering the content better by adding max-width and mx-auto -->
          <div class="max-w-6xl mx-auto">
            <div class="flex flex-col lg:flex-row items-center justify-between">
              <!-- Left Column -->
              <div class="lg:w-1/2 mb-10 lg:mb-0">
                <div class="bg-green-500 text-white py-2 px-6 rounded-lg inline-block mb-4">
                  Wablas with Multi Device and Multi Agent
                </div>
                <h2 class="text-2xl lg:text-3xl text-gray-800 mb-4">
                  WABLAS is an Whatsapp API Gateway for WhatsApp Business API. It allows developers to interact with WhatsApp's service without having to deal with the complexities of the WhatsApp protocol.
                </h2>
                <p class="text-lg text-gray-700 mb-8">
                  With WABLAS, developers can easily send and receive messages, make and receive calls, and manage contacts using a simple HTTP-based API.
                </p>
                <p class="text-lg text-gray-700">
                  By using WABLAS, developers can create WhatsApp-based applications such as chatbots, customer support systems, and automated message campaigns. WABLASprovides a wide range of functionalities such as sending and receiving messages, creating and managing contacts, and handling media files, among others.
                </p>
              </div>

              <!-- Right Column -->
              <div class="lg:w-1/2">
                <div class="bg-white p-4 rounded-lg shadow-lg">
                  <div class="bg-green-500 text-white py-1 px-4 rounded text-sm inline-block mb-2">
                    Wablas with Store Platform
                  </div>
                  <h3 class="text-xl font-semibold mb-2">Selling products via whatsapp catalog is easier with Wablas Store</h3>
                  <p class="text-sm text-gray-600 mb-4">
                    Easy manage orders, products, customers, shipping cost, to reports in one application.
                  </p>
                  <p class="text-sm text-gray-600">
                    And Whatsapp API gateway service for sending messages, notification, scheduler, reminder, and tracking with simple integration for your business
                  </p>
                  <img src="/api/placeholder/800/400" alt="Wablas Store Dashboard" class="mt-4 rounded-lg" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Second Part (Image 2) -->
      <div class="py-20 bg-white">
        <div class="container mx-auto px-6 text-center">
          <h2 class="text-3xl font-bold mb-6">Don't just engage, make it engaging.</h2>
          <p class="text-lg text-gray-600 mb-12 max-w-3xl mx-auto">
            Now you can send messages more interactively and reliably with several attributes that can be added, such as buttons, footers, headers and auto reply
          </p>

          <div class="flex justify-center">
            <div class="relative max-w-md">
              <img src="/api/placeholder/400/800" alt="Mobile App Demo" class="mx-auto" />

              <!-- Annotations -->
              <div class="absolute top-1/4 right-0 flex items-center">
                <div class="bg-green-500 text-white px-3 py-1 rounded-md text-sm">
                  footer
                </div>
                <div class="w-16 h-0.5 bg-black ml-2"></div>
              </div>

              <div class="absolute top-2/4 right-0 flex items-center">
                <div class="bg-green-500 text-white px-3 py-1 rounded-md text-sm">
                  Button Interaktif
                </div>
                <div class="w-16 h-0.5 bg-black ml-2"></div>
              </div>

              <div class="absolute bottom-1/4 right-0 flex items-center">
                <div class="bg-green-500 text-white px-3 py-1 rounded-md text-sm">
                  Auto Reply
                </div>
                <div class="w-16 h-0.5 bg-black ml-2"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section (Image 3 & 4) -->
    <section id="features" class="py-20 bg-gray-50">
      <div class="container mx-auto px-6">
        <!-- Added max-width and mx-auto to center the features content -->
        <div class="max-w-6xl mx-auto">
          <div class="text-center mb-16">
            <div class="bg-green-500 text-white py-2 px-6 rounded-lg inline-block mb-4">
              Our awesome features
            </div>
            <h2 class="text-3xl font-bold">We give <span class="font-normal">best features</span></h2>
          </div>

          <!-- Features Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Feature 1 -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-center mb-2">Auto Responder</h3>
              <p class="text-gray-600 text-center text-sm">
                Allows you to send automatic replies when people write to your numbers. It attends your customers at Whatsapp 24/7
              </p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-center mb-2">Whatsapp Rotator</h3>
              <p class="text-gray-600 text-center text-sm">
                WhatsApp rotator with single number is a feature that forwards and shares leads to customer service (CS)
              </p>
            </div>

            <!-- More features -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 010 7.75"></path>
                </svg>
              </div>
              <h3 class="text-lg font-bold text-center mb-2">Multi Agents</h3>
              <p class="text-gray-600 text-center text-sm">
                A single WhatsApp account that can be accessed by multiple admins at the same time.
              </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"></path>
                  <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
              </div>
              <h3 class="text-lg font-bold text-center mb-2">Unlimited Labels</h3>
              <p class="text-gray-600 text-center text-sm">
                Use the label feature to segment your customers.
              </p>
            </div>
          </div>

          <!-- Second row of features -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- More features here (similar structure) -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-bold text-center mb-2">Live Chats</h3>
              <p class="text-gray-600 text-center text-sm">
                Service that allows to answer all questions, complaints, request in real time.
              </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
              </div>
              <h3 class="text-lg font-bold text-center mb-2">Working Hours</h3>
              <p class="text-gray-600 text-center text-sm">
                Manage working hours for each agent to keep your business running well.
              </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-center mb-2">Auto Reply</h3>
              <p class="text-gray-600 text-center text-sm">
                Create a chatbot for frequently asked questions by your customers
              </p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
              <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3zM7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"></path>
                </svg>
              </div>
              <h3 class="text-lg font-bold text-center mb-2">Closing Greetings</h3>
              <p class="text-gray-600 text-center text-sm">
                Say closing greetings for each lead automatically
              </p>
            </div>
          </div>

          <!-- Integration Section (Image 4) -->
          <div class="mt-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
              <div class="grid grid-cols-3 gap-4">
                <!-- Integration Logos -->
                <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-center">
                  <div class="text-center">
                    <div class="font-bold text-sm">Google Form</div>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-center">
                  <div class="text-center">
                    <div class="font-bold text-sm">LandingPress</div>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-center">
                  <div class="text-center">
                    <div class="font-bold text-sm">LS Plugins</div>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-center">
                  <div class="text-center">
                    <div class="font-bold text-sm">OpenSID</div>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-center">
                  <div class="text-center">
                    <div class="font-bold text-sm">JIBAS</div>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm flex items-center justify-center">
                  <div class="text-center">
                    <div class="font-bold text-sm">WooCommerce</div>
                  </div>
                </div>
              </div>

              <div>
                <div class="bg-green-500 text-white py-2 px-6 rounded-lg inline-block mb-4">
                  Our awesome features
                </div>
                <h2 class="text-3xl font-bold mb-4">Wablas use <span class="font-normal">the tool you love</span></h2>
                <p class="text-gray-600">
                  Integrate Wablas with any tool, app or platform you like and use.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Other Sections -->
    <section id="clients" class="py-20 bg-white">
      <div class="container mx-auto px-6">
        <!-- Added max-width and mx-auto for consistency with other sections -->
        <div class="max-w-6xl mx-auto text-center">
          <h2 class="text-3xl font-bold mb-10">Our Clients</h2>
          <p class="text-gray-600 mb-10">This is a placeholder for the clients section.</p>
        </div>
      </div>
    </section>

    <!-- Footer Section (Image 5) -->
    <footer class="bg-gradient-to-b from-teal-500 to-teal-700 text-white">
      <div class="container mx-auto px-6 py-12">
        <!-- Added max-width and mx-auto to center the footer content -->
        <div class="max-w-6xl mx-auto">
          <div class="flex flex-col lg:flex-row justify-between mb-8">
            <div class="mb-10 lg:mb-0">
              <div class="flex items-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-4h2v2h-2zm0-10h2v8h-2z" />
                </svg>
                <div class="text-2xl font-bold">WABLAS<span class="text-sm align-top">v.2</span></div>
              </div>
              <p class="text-sm mb-2">By Wablas Indonesia</p>
              <p class="text-sm mb-6">Email: wablasdev@gmail.com</p>

              <div class="mb-6">
                <p class="font-bold mb-2">Open Hours (Fast Response):</p>
                <p class="text-sm">Mon - Sat: 8 am - 5 pm, Sunday: Slow Response</p>
              </div>
            </div>

            <div class="mb-10 lg:mb-0">
              <h3 class="text-xl font-bold mb-6">Links</h3>
              <ul class="space-y-3">
                <li><a href="#home" class="hover:text-green-200">Home</a></li>
                <li><a href="#features" class="hover:text-green-200">Features</a></li>
                <li><a href="#pricing" class="hover:text-green-200">Pricing</a></li>
                <li><a href="#" class="hover:text-green-200">Privacy & Policy</a></li>
                <li><a href="#" class="hover:text-green-200">Login</a></li>
              </ul>
            </div>

            <div class="mb-10 lg:mb-0">
              <h3 class="text-xl font-bold mb-6">Newsletter</h3>
              <p class="mb-4">Send us a newsletter to get update</p>
              <div class="flex">
                <input type="email" placeholder="Enter your email address" class="px-4 py-2 w-full rounded-l-md text-gray-800" />
                <button class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-r-md">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                  </svg>
                </button>
              </div>

              <div class="flex flex-wrap gap-2 mt-6">
                <!-- Social Icons would go here -->
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                  <span class="text-teal-600 text-xs">FB</span>
                </div>
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                  <span class="text-teal-600 text-xs">IG</span>
                </div>
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                  <span class="text-teal-600 text-xs">TW</span>
                </div>
              </div>
            </div>
          </div>

          <div class="border-t border-teal-400 pt-8 text-center text-sm">
            <p>2025 © All rights reserved by silvanix.com</p>
          </div>
        </div>
      </div>

      <!-- WhatsApp and Back to Top Buttons -->
      <div class="fixed bottom-6 right-6 space-y-4">
        <a href="#" class="flex items-center justify-center w-12 h-12 bg-green-500 rounded-full shadow-lg hover:bg-green-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
          </svg>
        </a>
        <button @click="scrollToSection('home')" class="flex items-center justify-center w-12 h-12 bg-teal-500 rounded-full shadow-lg hover:bg-teal-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="18 15 12 9 6 15"></polyline>
          </svg>
        </button>
      </div>
    </footer>
  </main>
</template>
