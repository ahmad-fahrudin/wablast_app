<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const page = usePage();
const user = page.props.auth.user;
const props = defineProps<{
    device: {
        deviceID: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Devices',
        href: '/devices',
    },
    {
        title: 'QR Code',
        href: '/devices/qrcode',
    },
];

const deviceId = ref(props.device.deviceID || '');
const qrCode = ref(null);
const error = ref(null);
const processingQR = ref(false);
const deviceStatus = ref(null);

const handleGenerateQR = async () => {
    // Show warning confirmation dialog before proceeding
    const result = await Swal.fire({
        title: 'Peringatan',
        html: `
            <div class="text-left">
                <p class="mb-3">Is your WhatsApp number more than 3 days old?</p>
                <p class="mb-3">Apakah Nomor WA anda sudah berumur lebih dari 3 hari?</p>
                <p class="mb-3">*If under 3 days old & you proceed, we're not responsible for potential bans.</p>
                <p class="mb-3">(Jika tidak & tetap melanjutkan resiko ke banned bukan tanggung jawab kami)</p>
                <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded-md mt-3">
                    <p class="font-semibold">Important</p>
                    <p class="my-2">If your WhatsApp number is blocked, please migrate to a new server, contact the admin, and then follow the instructions.</p>
                    <p>Jika nomor WhatsApp Anda diblokir, silakan migrasi ke server baru, hubungi admin, lalu ikuti petunjuknya.</p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
    });

    if (result.isConfirmed) {
        error.value = null;
        qrCode.value = null;
        processingQR.value = true;

        try {
            const response = await axios.get('/generate-qr', {
                params: { deviceId: deviceId.value }
            });

            if (response.data.error) {
                error.value = response.data.error;
            } else {
                // Store QR code data
                const qrCodeData = `data:image/png;base64,${response.data}`;
                qrCode.value = qrCodeData;

                // Display QR code in SweetAlert with 1-minute timer
                const swalInstance = Swal.fire({
                    title: 'Scan QR Code dengan WhatsApp',
                    html: `
                        <div class="flex flex-col items-center justify-center">
                            <div class="bg-white border-4 border-gray-200 rounded-md p-3 shadow-lg mb-4">
                                <img src="${qrCodeData}" alt="WhatsApp QR Code" class="w-full max-w-xs md:max-w-md h-auto object-contain" />
                            </div>
                            <div class="text-center max-w-md mx-auto">
                                <p class="text-sm text-gray-500">
                                    Scan QR code ini menggunakan aplikasi WhatsApp di ponsel Anda melalui menu Perangkat Tertaut.
                                </p>
                                <p class="text-sm text-red-600 font-bold mt-2">
                                    Tunggu 1 menit sebelum bisa menutup dialog.<br>
                                    Jika ada kendala hubungi admin via WA.
                                </p>
                                <p class="text-sm font-semibold mt-3" id="countdown-timer">Tunggu: 60 detik</p>
                            </div>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#3085d6',
                    width: 'auto',
                    allowOutsideClick: false,
                    didOpen: () => {
                        // Disable the confirm button initially
                        Swal.getConfirmButton().disabled = true;
                        Swal.getConfirmButton().classList.add('opacity-50', 'cursor-not-allowed');

                        // Set up countdown timer
                        let timerSeconds = 60;
                        const timerElement = document.getElementById('countdown-timer');

                        const countdown = setInterval(() => {
                            timerSeconds--;
                            if (timerElement) {
                                timerElement.textContent = `Tunggu: ${timerSeconds} detik`;
                            }

                            if (timerSeconds <= 0) {
                                clearInterval(countdown);
                                if (timerElement) {
                                    timerElement.textContent = 'Anda sekarang dapat menutup dialog ini';
                                    timerElement.classList.add('text-green-600');
                                }

                                // Enable the confirm button
                                Swal.getConfirmButton().disabled = false;
                                Swal.getConfirmButton().classList.remove('opacity-50', 'cursor-not-allowed');
                            }
                        }, 1000);
                    }
                }).then((result) => {
                    // When dialog is closed, hit the device-check route with deviceId parameter
                    router.visit(`/device-check?deviceId=${deviceId.value}`);
                });
            }
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to generate QR code. Please try again.';
        } finally {
            processingQR.value = false;
        }
    }
};
</script>

<template>
    <Head title="Device QR Code" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-primary">WhatsApp QR Code Generator</h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Tutorial Section - 6 columns -->
                <div class="md:col-span-6">
                    <Card class="h-full">
                        <CardContent>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <h3 class="text-lg font-medium">Langkah 1: Buka WhatsApp di Ponsel Anda</h3>
                                    <p class="text-gray-600 dark:text-gray-300">Pastikan Anda menggunakan aplikasi WhatsApp resmi yang terinstal di ponsel Anda.</p>
                                </div>

                                <div class="space-y-2">
                                    <h3 class="text-lg font-medium">Langkah 2: Buka Menu Perangkat Tertaut</h3>
                                    <p class="text-gray-600 dark:text-gray-300">
                                        <span class="font-medium">Untuk Android:</span> Ketuk ikon tiga titik (⋮) di sudut kanan atas > Ketuk <b>Perangkat Tertaut</b>.
                                    </p>
                                    <p class="text-gray-600 dark:text-gray-300">
                                        <span class="font-medium">Untuk iPhone:</span> Ketuk <b>Pengaturan</b> di bagian bawah layar > Ketuk <b>Perangkat Tertaut</b>.
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <h3 class="text-lg font-medium">Langkah 3: Menautkan Perangkat Baru</h3>
                                    <p class="text-gray-600 dark:text-gray-300">Ketuk <b>Tautkan Perangkat</b> atau <b>Tautkan Perangkat Baru</b>.</p>
                                </div>

                                <div class="space-y-2">
                                    <h3 class="text-lg font-medium">Langkah 4: Scan QR Code</h3>
                                    <p class="text-gray-600 dark:text-gray-300">Arahkan kamera ponsel Anda ke QR code yang muncul di layar komputer atau perangkat Anda.</p>
                                </div>

                                <div class="space-y-2">
                                    <h3 class="text-lg font-medium">Langkah 5: Pemberitahuan Keamanan</h3>
                                    <p class="text-gray-600 dark:text-gray-300">Jika ini pertama kali Anda menautkan perangkat, Anda akan melihat penjelasan tentang fitur ini. Ketuk <b>Lanjutkan</b> untuk melanjutkan.</p>
                                </div>

                                <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-md">
                                    <h3 class="text-lg font-medium mb-2">Tips untuk Menautkan Perangkat:</h3>
                                    <ul class="list-disc pl-5 space-y-1 text-gray-600 dark:text-gray-300">
                                        <li>Pastikan koneksi internet ponsel dan perangkat ini stabil</li>
                                        <li>Jika QR code kedaluwarsa, generate ulang dengan menekan tombol "Generate QR Code"</li>
                                        <li>Pastikan kamera ponsel Anda dapat memindai QR code dengan jelas</li>
                                        <li>Jika Anda mengalami masalah, restart WhatsApp di ponsel Anda dan coba lagi</li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- QR Code Display - 6 columns -->
                <div class="md:col-span-6">
                    <Card class="mb-6">
                        <CardContent>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="deviceId">Device ID</Label>
                                    <Input
                                        id="deviceId"
                                        v-model="deviceId"
                                        readonly
                                        disabled
                                        class="bg-gray-50 dark:bg-gray-800"
                                    />
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter>
                            <Button
                                type="button"
                                class="w-full px-6"
                                :disabled="processingQR || deviceStatus === 'connected' || !deviceId"
                                @click="handleGenerateQR"
                            >
                                <span v-if="processingQR" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Generating QR Code...
                                </span>
                                <span v-else class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    Generate QR Code
                                </span>
                            </Button>
                        </CardFooter>
                    </Card>

                    <!-- Warning Alert -->
                    <Card class="mb-6 border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-900">
                        <CardContent class="p-4">
                            <div class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <h4 class="font-semibold text-amber-800 dark:text-amber-300 mb-1">Peringatan!</h4>
                                    <p class="text-amber-700 dark:text-amber-200">Nomor WhatsApp yang digunakan untuk pemindaian harus berusia minimal 3 hari dan aktif digunakan untuk mengobrol agar tidak diblokir sebagai bot.</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Error Display -->
                    <Card v-if="error" class="mb-6 border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-900">
                        <CardContent class="p-4">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-red-700 dark:text-red-300 font-medium">{{ error }}</span>
                            </div>
                        </CardContent>
                    </Card>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
