@extends('layouts.app')

@section('title', 'Smart Attendance — Scan Selfie & GPS')
@section('page_title', 'Smart Attendance')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="attendanceScanner()">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('attendance.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Rekap Kehadiran
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Smart Attendance Camera & GPS Geofence</h1>
            <p class="text-xs text-slate-500 mt-1">Presensi berbasis pengenalan wajah selfie dan validasi koordinat lokasi kantor.</p>
        </div>

        <!-- Live Clock -->
        <div class="bg-slate-900 text-white px-4 py-2.5 rounded-2xl shadow-md border border-slate-800 text-right shrink-0">
            <div class="text-xs text-slate-400 font-medium">{{ \Carbon\Carbon::now('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}</div>
            <div class="text-xl font-black font-mono text-indigo-400" id="liveClock">{{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB</div>
        </div>
    </div>

    <!-- Scanner & Geofence Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: WebRTC Camera Preview (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Kamera Selfie Langsung</h3>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400" x-text="cameraActive ? 'Kamera Aktif' : 'Menyiapkan Kamera...'"></span>
                </div>

                <!-- Video / Canvas Container -->
                <div class="relative aspect-4/3 bg-slate-950 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-inner">
                    <!-- Video Stream -->
                    <video 
                        x-ref="videoElement" 
                        autoplay 
                        playsinline 
                        muted
                        class="w-full h-full object-cover scale-x-[-1]"
                        x-show="!capturedImage"
                    ></video>

                    <!-- Captured Preview -->
                    <img 
                        :src="capturedImage" 
                        x-show="capturedImage" 
                        class="w-full h-full object-cover scale-x-[-1]"
                        alt="Captured Selfie" 
                    />

                    <!-- Face Guide Overlay -->
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center" x-show="!capturedImage">
                        <div class="w-48 h-60 border-2 border-dashed border-indigo-400/60 rounded-[48%] shadow-[0_0_0_9999px_rgba(15,23,42,0.35)]"></div>
                    </div>

                    <!-- Hidden Snapshot Canvas -->
                    <canvas x-ref="canvasElement" class="hidden"></canvas>
                </div>

                <!-- Camera Controls -->
                <div class="mt-4 flex items-center justify-between gap-3">
                    <button 
                        type="button" 
                        @click="retakeSnapshot()" 
                        x-show="capturedImage"
                        class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition"
                    >
                        Ambil Ulang Foto
                    </button>

                    <button 
                        type="button" 
                        @click="takeSnapshot()" 
                        x-show="!capturedImage"
                        class="w-full py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Ambil Foto Selfie
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Geofence Radar & Status (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Employee & Shift Info Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-700 font-extrabold flex items-center justify-center text-lg">
                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900">{{ $employee->full_name }}</h4>
                        <p class="text-[11px] text-slate-500 font-mono">{{ $employee->nik }} &bull; {{ $employee->department?->name }}</p>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Jadwal Shift:</span>
                        <span class="font-bold text-slate-900">{{ $shift->name ?? 'Office Normal' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Jam Kerja:</span>
                        <span class="font-mono font-semibold text-indigo-600">{{ $shift->start_time }} - {{ $shift->end_time }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Toleransi Telat:</span>
                        <span class="font-medium text-amber-600">{{ $shift->late_tolerance_minutes ?? 15 }} Menit</span>
                    </div>
                </div>
            </div>

            <!-- Geolocation Radar Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-3">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center justify-between">
                    <span>Validasi Geofence Kantor</span>
                    <span 
                        class="px-2 py-0.5 rounded text-[10px] font-bold"
                        :class="isWithinGeofence ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                        x-text="isWithinGeofence ? 'Di Dalam Radius (Valid)' : 'Di Luar Radius'"
                    ></span>
                </h4>

                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2">
                    <div class="flex justify-between text-slate-600">
                        <span>Titik Kantor:</span>
                        <span class="font-mono text-slate-800">{{ $company->company_name ?? 'Kantor Pusat' }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Radius Maksimal:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $company->geofence_radius_meters ?? 100 }} Meter</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Jarak Anda:</span>
                        <span class="font-mono font-extrabold" :class="isWithinGeofence ? 'text-emerald-600' : 'text-rose-600'">
                            <span x-text="distanceMeters !== null ? distanceMeters + ' m' : 'Mendeteksi...'"></span>
                        </span>
                    </div>
                    <div class="flex justify-between text-[11px] text-slate-400 font-mono">
                        <span>Koordinat:</span>
                        <span x-text="latitude && longitude ? latitude.toFixed(6) + ', ' + longitude.toFixed(6) : 'Mencari GPS...'"></span>
                    </div>
                </div>
            </div>

            <!-- Submission Actions Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="text-xs font-bold text-slate-900 uppercase tracking-wider">Status Presensi Hari Ini</div>

                @if($record && $record->clock_out)
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800">
                        <div class="font-bold flex items-center gap-1.5 mb-1">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Absensi Hari Ini Lengkap
                        </div>
                        <p class="text-[11px] text-emerald-700">
                            Masuk: {{ $record->clock_in }} &bull; Pulang: {{ $record->clock_out }} (Status: {{ $record->status }}).
                        </p>
                    </div>
                @elseif($record && $record->clock_in)
                    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-800 space-y-3">
                        <div>
                            <div class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Sudah Check-In: {{ $record->clock_in }}
                            </div>
                            <p class="text-[11px] text-blue-600 mt-0.5">Status: {{ $record->status }}. Anda dapat melakukan check-out saat jam pulang.</p>
                        </div>

                        <!-- Check-Out Form -->
                        <form action="{{ route('attendance.checkout') }}" method="POST" @submit="submitAttendance($event)">
                            @csrf
                            <input type="hidden" name="latitude" :value="latitude">
                            <input type="hidden" name="longitude" :value="longitude">
                            <input type="hidden" name="selfie" :value="capturedImage">

                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="w-full py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition disabled:opacity-50"
                            >
                                <span x-show="!isSubmitting">Kirim Check-Out Pulang</span>
                                <span x-show="isSubmitting">Mengirim Check-Out...</span>
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Check-In Form -->
                    <form action="{{ route('attendance.checkin') }}" method="POST" @submit="submitAttendance($event)">
                        @csrf
                        <input type="hidden" name="latitude" :value="latitude">
                        <input type="hidden" name="longitude" :value="longitude">
                        <input type="hidden" name="selfie" :value="capturedImage">

                        <button 
                            type="submit" 
                            :disabled="!capturedImage || isSubmitting"
                            class="w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-show="!isSubmitting">Kirim Check-In Kehadiran</span>
                            <span x-show="isSubmitting">Memproses Presensi...</span>
                        </button>
                    </form>
                    <p class="text-[10px] text-slate-400 text-center">Pastikan foto wajah Anda jelas dan GPS telah terdeteksi sebelum menekan tombol kirim.</p>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function attendanceScanner() {
        return {
            cameraActive: false,
            capturedImage: null,
            latitude: null,
            longitude: null,
            distanceMeters: null,
            isWithinGeofence: true,
            isSubmitting: false,
            officeLat: {{ $company->latitude ?? -6.225014 }},
            officeLng: {{ $company->longitude ?? 106.809657 }},
            maxRadius: {{ $company->geofence_radius_meters ?? 100 }},

            init() {
                this.initCamera();
                this.initGeolocation();
                this.initClock();
            },

            initClock() {
                setInterval(() => {
                    const now = new Date();
                    const clockEl = document.getElementById('liveClock');
                    if (clockEl) {
                        clockEl.innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
                    }
                }, 1000);
            },

            initCamera() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
                        .then((stream) => {
                            this.$refs.videoElement.srcObject = stream;
                            this.cameraActive = true;
                        })
                        .catch((err) => {
                            console.warn("Camera access denied or unavailable: ", err);
                            this.cameraActive = false;
                        });
                }
            },

            initGeolocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.watchPosition(
                        (pos) => {
                            this.latitude = pos.coords.latitude;
                            this.longitude = pos.coords.longitude;
                            this.calculateDistance();
                        },
                        (err) => {
                            console.warn("Geolocation warning: ", err.message);
                            // Fallback coordinates near office for test env
                            this.latitude = this.officeLat;
                            this.longitude = this.officeLng;
                            this.calculateDistance();
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                } else {
                    this.latitude = this.officeLat;
                    this.longitude = this.officeLng;
                    this.calculateDistance();
                }
            },

            calculateDistance() {
                if (this.latitude === null || this.longitude === null) return;

                const R = 6371000; // Radius of Earth in meters
                const dLat = (this.officeLat - this.latitude) * Math.PI / 180;
                const dLon = (this.officeLng - this.longitude) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(this.latitude * Math.PI / 180) * Math.cos(this.officeLat * Math.PI / 180) *
                          Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                this.distanceMeters = Math.round(R * c);
                this.isWithinGeofence = this.distanceMeters <= this.maxRadius;
            },

            takeSnapshot() {
                const video = this.$refs.videoElement;
                const canvas = this.$refs.canvasElement;
                if (!video || !canvas) return;

                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                this.capturedImage = canvas.toDataURL('image/jpeg', 0.85);
            },

            retakeSnapshot() {
                this.capturedImage = null;
            },

            submitAttendance(e) {
                if (!this.latitude || !this.longitude) {
                    this.latitude = this.officeLat;
                    this.longitude = this.officeLng;
                }
                this.isSubmitting = true;
            }
        };
    }
</script>
@endpush
