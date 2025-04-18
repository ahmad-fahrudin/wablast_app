<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { PlusIcon, SearchIcon, CheckIcon, MessageSquareIcon, UsersIcon, TagIcon, ClockIcon, SmileIcon, PhoneIcon } from 'lucide-vue-next';

// Navigation sections
const sections = ref([
  { id: 'home', label: 'Home' },
  { id: 'features', label: 'Features' },
  { id: 'clients', label: 'Clients' },
  { id: 'pricing', label: 'Pricing' },
  { id: 'api', label: 'API' },
]);

// Mobile navigation state
const mobileMenuOpen = ref(false);

// Active section tracking for navigation highlight
const activeSection = ref('home');
const scrollThreshold = 100;

// Scroll to specific section
const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
    activeSection.value = sectionId;
    mobileMenuOpen.value = false; // Close mobile menu after selection
  }
};

// Features data
const features = [
  {
    icon: MessageSquareIcon,
    title: 'Auto Responder',
    description: 'Allows you to send automatic replies when people write to your numbers. It attends your customers at Whatsapp 24/7'
  },
  {
    icon: PhoneIcon,
    title: 'Whatsapp Rotator',
    description: 'WhatsApp rotator with single number is a feature that forwards and shares leads to customer service (CS)'
  },
  {
    icon: UsersIcon,
    title: 'Multi Agents',
    description: 'A single WhatsApp account that can be accessed by multiple admins at the same time.'
  },
  {
    icon: TagIcon,
    title: 'Unlimited Labels',
    description: 'Use the label feature to segment your customers.'
  },
  {
    icon: MessageSquareIcon,
    title: 'Live Chats',
    description: 'Service that allows to answer all questions, complaints, request in real time.'
  },
  {
    icon: ClockIcon,
    title: 'Working Hours',
    description: 'Manage working hours for each agent to keep your business running well.'
  },
  {
    icon: MessageSquareIcon,
    title: 'Auto Reply',
    description: 'Create a chatbot for frequently asked questions by your customers'
  },
  {
    icon: SmileIcon,
    title: 'Closing Greetings',
    description: 'Say closing greetings for each lead automatically'
  }
];

// Integration partners
const integrations = [
  'Google Form',
  'LandingPress',
  'LS Plugins',
  'OpenSID',
  'JIBAS',
  'WooCommerce'
];

// Update active section based on scroll position
onMounted(() => {
  const handleScroll = () => {
    const scrollPosition = window.scrollY;

    // Get all section elements
    const sectionElements = sections.value.map(section => {
      const element = document.getElementById(section.id);
      return {
        id: section.id,
        offsetTop: element?.offsetTop || 0,
        height: element?.offsetHeight || 0
      };
    });

    // Find the current active section
    for (let i = sectionElements.length - 1; i >= 0; i--) {
      const section = sectionElements[i];
      if (scrollPosition >= section.offsetTop - scrollThreshold) {
        activeSection.value = section.id;
        break;
      }
    }
  };

  window.addEventListener('scroll', handleScroll);
  // Initial check
  handleScroll();

  // Clean up event listener
  return () => {
    window.removeEventListener('scroll', handleScroll);
  };
});
</script>

<template>
  <Head title="WABLAS - WhatsApp Business API Gateway">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    <meta name="description" content="WABLAS is a complete WhatsApp API Gateway for WhatsApp Business API with multi-device and multi-agent capabilities" />
  </Head>

  <!-- Fixed Header -->
  <header class="fixed top-0 left-0 right-0 bg-white bg-opacity-95 backdrop-blur-sm z-50 shadow-sm">
    <div class="container mx-auto px-6 py-4">
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex items-center">
          <div class="text-2xl font-bold text-green-600 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-4h2v2h-2zm0-10h2v8h-2z" />
            </svg>
            WABLAS<span class="text-sm align-top font-light">v.2</span>
          </div>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex space-x-6">
          <button
            v-for="section in sections"
            :key="section.id"
            @click="scrollToSection(section.id)"
            class="text-gray-700 hover:text-green-600 font-medium transition-colors relative"
            :class="{ 'text-green-600': activeSection === section.id }"
          >
            {{ section.label }}
            <span
              v-if="activeSection === section.id"
              class="absolute bottom-0 left-0 w-full h-0.5 bg-green-600 rounded-full"
            ></span>
          </button>
          <Link :href="route('login')" class="text-gray-700 hover:text-green-600 font-medium">
            Login
          </Link>
          <Link :href="route('register')" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
            Register
          </Link>
        </nav>

        <!-- Mobile menu button -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden text-gray-700 hover:text-green-600 focus:outline-none"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path v-if="mobileMenuOpen" strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            <path v-else strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div
      v-if="mobileMenuOpen"
      class="md:hidden bg-white border-t border-gray-100 shadow-lg"
    >
      <div class="py-3 space-y-1">
        <button
          v-for="section in sections"
          :key="section.id"
          @click="scrollToSection(section.id)"
          class="block px-6 py-3 w-full text-left text-gray-700 hover:bg-gray-50 hover:text-green-600 transition-colors"
          :class="{ 'text-green-600 bg-gray-50': activeSection === section.id }"
        >
          {{ section.label }}
        </button>
        <div class="px-6 py-3 flex space-x-3">
          <Link :href="route('login')" class="block py-2 px-4 text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 text-center flex-1">
            Login
          </Link>
          <Link :href="route('register')" class="block py-2 px-4 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center flex-1">
            Register
          </Link>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content with Sections -->
  <main class="pt-20">
    <!-- Hero Section -->
    <section id="home" class="relative overflow-hidden bg-gradient-to-b from-white to-gray-50 py-20 md:py-32">
      <div class="container mx-auto px-6">
        <div class="max-w-7xl mx-auto">
          <div class="flex flex-col lg:flex-row items-center justify-between gap-10">
            <!-- Left Column - Hero Content -->
            <div class="lg:w-1/2 z-10">
              <div class="inline-block bg-green-100 text-green-800 font-medium py-1 px-4 rounded-full text-sm mb-6">
                Wablas with Multi Device and Multi Agent
              </div>
              <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                The Ultimate <span class="text-green-600">WhatsApp Business API</span> Gateway
              </h1>
              <p class="text-lg text-gray-700 mb-8 leading-relaxed">
                WABLAS allows developers to interact with WhatsApp's service without dealing with the complexities of the protocol. Create WhatsApp-based applications like chatbots, customer support systems, and automated campaigns.
              </p>
              <div class="flex flex-col sm:flex-row gap-4">
                <Link :href="route('register')" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors text-center font-medium shadow-sm">
                  Get Started
                </Link>
                <a href="#features" @click.prevent="scrollToSection('features')" class="border border-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-50 transition-colors text-center font-medium">
                  Learn More
                </a>
              </div>
            </div>

            <!-- Right Column - Hero Image -->
            <div class="lg:w-1/2 relative">
              <div class="bg-white p-6 rounded-2xl shadow-xl">
                <div class="bg-green-100 text-green-800 font-medium py-1 px-3 rounded-lg text-sm inline-block mb-3">
                  Wablas with Store Platform
                </div>
                <h3 class="text-xl font-bold mb-3">Simplified WhatsApp Catalog Management</h3>
                <p class="text-gray-600 mb-4">
                  Easily manage orders, products, customers, shipping, and reports in one intuitive application.
                </p>
                <img src="/api/placeholder/800/400" alt="Wablas Dashboard" class="rounded-lg shadow-sm w-full" />

                <!-- Decorative elements -->
                <div class="absolute -top-4 -right-4 h-20 w-20 bg-green-500 rounded-full opacity-20"></div>
                <div class="absolute -bottom-4 -left-4 h-12 w-12 bg-blue-500 rounded-full opacity-20"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Background decorations -->
      <div class="absolute top-1/3 right-0 h-64 w-64 bg-green-400 rounded-full filter blur-3xl opacity-10"></div>
      <div class="absolute bottom-1/4 left-10 h-40 w-40 bg-blue-400 rounded-full filter blur-3xl opacity-10"></div>
    </section>

    <!-- Interactive Messaging Section -->
    <section class="py-20 bg-white relative overflow-hidden">
      <div class="container mx-auto px-6">
        <div class="max-w-7xl mx-auto text-center">
          <h2 class="text-3xl md:text-4xl font-bold mb-6">Don't just engage, make it engaging</h2>
          <p class="text-lg text-gray-600 mb-16 max-w-3xl mx-auto">
            Send messages more interactively and reliably with several attributes like buttons, footers, headers, and auto-reply features
          </p>

          <div class="relative mx-auto max-w-md">
            <div class="bg-gray-900 rounded-3xl overflow-hidden p-4 shadow-2xl">
              <div class="bg-green-600 w-16 h-1 rounded-full mx-auto mb-2"></div>
              <div class="p-2 rounded-2xl overflow-hidden">
                <img src="/api/placeholder/400/700" alt="Mobile App Demo" class="rounded-xl mx-auto" />
              </div>
            </div>

            <!-- Interactive annotations -->
            <div class="absolute top-1/4 -right-16 sm:right-0 flex items-center">
              <div class="bg-green-600 text-white px-3 py-1 rounded-md text-sm shadow-lg">
                Custom Header
              </div>
              <div class="w-12 h-0.5 bg-green-600 ml-2"></div>
            </div>

            <div class="absolute top-2/4 -right-16 sm:right-0 flex items-center">
              <div class="bg-green-600 text-white px-3 py-1 rounded-md text-sm shadow-lg">
                Interactive Buttons
              </div>
              <div class="w-12 h-0.5 bg-green-600 ml-2"></div>
            </div>

            <div class="absolute bottom-1/4 -right-16 sm:right-0 flex items-center">
              <div class="bg-green-600 text-white px-3 py-1 rounded-md text-sm shadow-lg">
                Smart Auto-Reply
              </div>
              <div class="w-12 h-0.5 bg-green-600 ml-2"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Background decorations -->
      <div class="absolute top-1/2 left-0 h-96 w-96 bg-green-400 rounded-full filter blur-3xl opacity-5"></div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50 relative overflow-hidden">
      <div class="container mx-auto px-6">
        <div class="max-w-7xl mx-auto">
          <div class="text-center mb-16">
            <div class="inline-block bg-green-100 text-green-800 font-medium py-1 px-4 rounded-full text-sm mb-3">
              Powerful Features
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-4">We give <span class="text-green-600">the best features</span></h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
              Everything you need to create an exceptional WhatsApp business experience for your customers
            </p>
          </div>

          <!-- Features Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div v-for="(feature, index) in features" :key="index"
                 class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-100">
              <div class="bg-green-50 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                <component :is="feature.icon" class="h-6 w-6 text-green-600" />
              </div>
              <h3 class="text-lg font-bold mb-2">{{ feature.title }}</h3>
              <p class="text-gray-600 text-sm">
                {{ feature.description }}
              </p>
            </div>
          </div>

          <!-- Integration Section -->
          <div class="mt-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
              <div>
                <div class="inline-block bg-green-100 text-green-800 font-medium py-1 px-4 rounded-full text-sm mb-3">
                  Seamless Integration
                </div>
                <h2 class="text-3xl font-bold mb-4">Use <span class="text-green-600">the tools you love</span></h2>
                <p class="text-lg text-gray-600 mb-6">
                  Integrate WABLAS with your favorite apps, tools, and platforms for a seamless workflow.
                </p>
                <a href="#" class="text-green-600 font-medium flex items-center hover:underline">
                  Learn about our API
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </a>
              </div>

              <div class="grid grid-cols-3 gap-4">
                <div v-for="integration in integrations" :key="integration"
                     class="bg-white p-4 rounded-xl shadow-sm flex items-center justify-center border border-gray-100 h-24">
                  <div class="text-center">
                    <div class="font-medium text-gray-800">{{ integration }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Background decorations -->
      <div class="absolute bottom-10 right-10 h-64 w-64 bg-green-400 rounded-full filter blur-3xl opacity-10"></div>
    </section>

    <!-- Clients Section -->
    <section id="clients" class="py-20 bg-white">
      <div class="container mx-auto px-6">
        <div class="max-w-7xl mx-auto text-center">
          <div class="inline-block bg-green-100 text-green-800 font-medium py-1 px-4 rounded-full text-sm mb-3">
            Trusted by Businesses
          </div>
          <h2 class="text-3xl md:text-4xl font-bold mb-16">Our Clients</h2>

          <!-- Client logos would go here -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 items-center justify-items-center">
            <div class="w-32 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
              <span class="text-gray-400 font-medium">Client 1</span>
            </div>
            <div class="w-32 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
              <span class="text-gray-400 font-medium">Client 2</span>
            </div>
            <div class="w-32 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
              <span class="text-gray-400 font-medium">Client 3</span>
            </div>
            <div class="w-32 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
              <span class="text-gray-400 font-medium">Client 4</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section - Update as needed -->
    <section id="pricing" class="py-20 bg-gray-50">
      <div class="container mx-auto px-6">
        <div class="max-w-7xl mx-auto text-center">
          <div class="inline-block bg-green-100 text-green-800 font-medium py-1 px-4 rounded-full text-sm mb-3">
            Affordable Plans
          </div>
          <h2 class="text-3xl md:text-4xl font-bold mb-4">Simple, transparent pricing</h2>
          <p class="text-lg text-gray-600 mb-16 max-w-2xl mx-auto">
            Choose the plan that works best for your business needs
          </p>

          <!-- Add pricing information or link to pricing page -->
          <div class="text-center">
            <Link :href="route('subscriptions.index')" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors inline-block font-medium shadow-sm">
              View Pricing Plans
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- API Section -->
    <section id="api" class="py-20 bg-white">
      <div class="container mx-auto px-6">
        <div class="max-w-7xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
              <div class="inline-block bg-green-100 text-green-800 font-medium py-1 px-4 rounded-full text-sm mb-3">
                Developer Friendly
              </div>
              <h2 class="text-3xl md:text-4xl font-bold mb-4">Powerful API for Developers</h2>
              <p class="text-lg text-gray-600 mb-6">
                Our robust API allows you to integrate WhatsApp messaging capabilities into your applications with ease.
              </p>

              <div class="space-y-4 mb-8">
                <div class="flex items-start">
                  <div class="flex-shrink-0 mt-1">
                    <CheckIcon class="h-5 w-5 text-green-600" />
                  </div>
                  <div class="ml-3">
                    <h4 class="text-lg font-medium">Easy Integration</h4>
                    <p class="text-gray-600">Simple HTTP-based API for quick implementation</p>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex-shrink-0 mt-1">
                    <CheckIcon class="h-5 w-5 text-green-600" />
                  </div>
                  <div class="ml-3">
                    <h4 class="text-lg font-medium">Comprehensive Documentation</h4>
                    <p class="text-gray-600">Detailed guides and examples to get you started</p>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex-shrink-0 mt-1">
                    <CheckIcon class="h-5 w-5 text-green-600" />
                  </div>
                  <div class="ml-3">
                    <h4 class="text-lg font-medium">Webhooks</h4>
                    <p class="text-gray-600">Real-time notifications for messages and events</p>
                  </div>
                </div>
              </div>

              <a href="#" class="bg-gray-900 text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors inline-block font-medium">
                Read API Documentation
              </a>
            </div>

            <!-- Code example box -->
            <div class="bg-gray-900 rounded-xl p-6 text-white shadow-xl">
              <div class="flex items-center justify-between mb-4">
                <div class="flex space-x-2">
                  <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                  <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                  <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                <div class="text-xs text-gray-400">WABLAS API Example</div>
              </div>

              <pre class="text-sm font-mono text-green-400"><code>// Send a message using WABLAS API
fetch('https://api.wablas.com/api/send-message', {
  method: 'POST',
  headers: {
    'Authorization': 'YOUR_API_KEY',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    phone: '6281234567890',
    message: 'Hello from WABLAS API!',
    type: 'text'
  })
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-green-600 to-green-700 text-white">
      <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto text-center">
          <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to transform your WhatsApp business?</h2>
          <p class="text-lg text-green-100 mb-8 max-w-2xl mx-auto">
            Join thousands of businesses that use WABLAS to engage with their customers
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <Link :href="route('register')" class="bg-white text-green-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors text-center font-medium shadow-sm">
              Create Account
            </Link>
            <a href="#features" @click.prevent="scrollToSection('features')" class="border border-white text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors text-center font-medium">
              Learn More
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
      <div class="container mx-auto px-6 py-12">
        <div class="max-w-7xl mx-auto">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            <!-- Company Info -->
            <div>
              <div class="flex items-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-4h2v2h-2zm0-10h2v8h-2z" />
                </svg>
                <div class="text-2xl font-bold text-white">WABLAS<span class="text-sm align-top text-green-500">v.2</span></div>
              </div>
              <p class="text-gray-400 mb-2">By Wablas Indonesia</p>
              <p class="text-gray-400 mb-6">Email: wablasdev@gmail.com</p>

              <div class="mb-6">
                <p class="font-bold text-white mb-2">Open Hours (Fast Response):</p>
                <p class="text-gray-400">Mon - Sat: 8 am - 5 pm</p>
                <p class="text-gray-400">Sunday: Slow Response</p>
              </div>
            </div>

            <!-- Quick Links -->
            <div>
              <h3 class="text-lg font-bold mb-6">Quick Links</h3>
              <ul class="space-y-3">
                <li><a href="#home" @click.prevent="scrollToSection('home')" class="text-gray-400 hover:text-green-500 transition-colors">Home</a></li>
                <li><a href="#features" @click.prevent="scrollToSection('features')" class="text-gray-400 hover:text-green-500 transition-colors">Features</a></li>
                <li><a href="#pricing" @click.prevent="scrollToSection('pricing')" class="text-gray-400 hover:text-green-500 transition-colors">Pricing</a></li>
                <li><a href="#api" @click.prevent="scrollToSection('api')" class="text-gray-400 hover:text-green-500 transition-colors">API</a></li>
                <li><a href="#" class="text-gray-400 hover:text-green-500 transition-colors">Privacy Policy</a></li>
                <li><a href="#" class="text-gray-400 hover:text-green-500 transition-colors">Terms of Service</a></li>
              </ul>
            </div>

            <!-- Account -->
            <div>
              <h3 class="text-lg font-bold mb-6">Account</h3>
              <ul class="space-y-3">
                <li><Link :href="route('login')" class="text-gray-400 hover:text-green-500 transition-colors">Login</Link></li>
                <li><Link :href="route('register')" class="text-gray-400 hover:text-green-500 transition-colors">Register</Link></li>
                <li><a href="#" class="text-gray-400 hover:text-green-500 transition-colors">Dashboard</a></li>
                <li><a href="#" class="text-gray-400 hover:text-green-500 transition-colors">Support</a></li>
              </ul>
            </div>

            <!-- Newsletter -->
            <div>
              <h3 class="text-lg font-bold mb-6">Newsletter</h3>
              <p class="text-gray-400 mb-4">Stay updated with our latest features and news</p>
              <div class="flex mb-4">
                <input type="email" placeholder="Your email address" class="px-4 py-2 w-full rounded-l-lg text-gray-800 focus:outline-none" />
                <button class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-r-lg transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                  </svg>
                </button>
              </div>

              <div class="flex space-x-3">
                <!-- Social Icons -->
                <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-full flex items-center justify-center transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
                  </svg>
                </a>
                <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-full flex items-center justify-center transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                  </svg>
                </a>
                <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-full flex items-center justify-center transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <div class="border-t border-gray-800 mt-12 pt-8 text-center text-sm text-gray-400">
            <p>© 2025 WABLAS. All rights reserved by silvanix.com</p>
          </div>
        </div>
      </div>
    </footer>

    <!-- Floating Buttons -->
    <div class="fixed bottom-6 right-6 space-y-4 z-20">
      <a href="https://wa.me/yourphonenumber" class="flex items-center justify-center w-12 h-12 bg-green-600 rounded-full shadow-lg hover:bg-green-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
        </svg>
      </a>
      <button @click="scrollToSection('home')" class="flex items-center justify-center w-12 h-12 bg-gray-800 rounded-full shadow-lg hover:bg-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
      </button>
    </div>
  </main>
</template>

<style>
/* Add smooth scrolling behavior */
html {
  scroll-behavior: smooth;
}

/* Add some animations and transitions */
.bg-gradient-to-b {
  background-image: linear-gradient(to bottom, var(--tw-gradient-stops));
}

/* Optional: Add backdrop filter support for modern browsers */
@supports (backdrop-filter: blur(10px)) {
  .backdrop-blur-sm {
    backdrop-filter: blur(4px);
  }
}
</style>
