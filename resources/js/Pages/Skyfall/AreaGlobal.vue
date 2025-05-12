<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import HeaderSection from "@/Components/Common/HeaderSection.vue";
import Bar3D from "@/Components/Charts/Bar3D.vue";
import { ref, computed, onMounted, watch, getCurrentInstance } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useAreaGlobalStore } from "@/stores/areaGlobalStore";
import axios from "axios";
import { toast } from 'vue3-toastify';
import { useFormat } from "@/composables/useFormat";

const props = defineProps({
    resumen: {
        type: Object,
        required: true,
    },
    sumas: {
        type: Object,
        required: true,
    },
});

const globalStore = useAreaGlobalStore();
const page = usePage();
const instance = getCurrentInstance();
const $t = instance?.proxy.$t ?? ((key) => key);
const { formatNumber } = useFormat();

// Inicializar sumas inmediatamente con valores por defecto
globalStore.setSumas({
    propuestos: props.sumas?.propuestos ?? 0,
    verificados: props.sumas?.verificados ?? 0,
    totales: props.sumas?.totales ?? 0,
});

// Estado reactivo
const isMenuOpen = ref(false);
const selectedBelongingId = ref(null);
const scrollContainer = ref(null);
const cardBgColor = ref("#e0f2fe");
const chartProposedColor = ref("#8BC34A");
const chartVerifiedColor = ref("#3E8E41");
const textColor = ref("#333333");
const textFont = ref("Figtree");

// Opciones de personalización
const fontOptions = [
    { value: "Figtree", label: "Figtree" },
    { value: "Inter", label: "Inter" },
    { value: "Roboto", label: "Roboto" },
    { value: "Arial", label: "Arial" },
    { value: "Poppins", label: "Poppins" },
    { value: "Lato", label: "Lato" },
    { value: "Montserrat", label: "Montserrat" },
];

// Convertir rgb/rgba a hex
const toHexColor = (color) => {
    if (!color || color.startsWith("#")) return color;

    const rgbMatch = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (rgbMatch) {
        const [, r, g, b] = rgbMatch;
        const toHex = (num) => {
            const hex = Number(num).toString(16).padStart(2, "0");
            return hex.length === 1 ? "0" + hex : hex;
        };
        return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
    }

    console.warn("Color no válido:", color);
    return "#000000";
};

// Cargar datos iniciales
onMounted(() => {
    globalStore.setUser({
        name: page.props.auth?.user?.name || "",
        profile_photo_url:
            page.props.auth?.user?.profile_photo_url || "/default-profile.png",
    });
    globalStore.fetchGlobalData();
    loadPreferences();
});

// Cargar preferencias
const loadPreferences = async () => {
    try {
        const response = await axios.get("/skyfall/user/preferences", {
            withCredentials: true,
        });
        cardBgColor.value = toHexColor(
            response.data.card_bg_color || "#e0f2fe"
        );
        chartProposedColor.value = toHexColor(
            response.data.chart_proposed_color || "#8BC34A"
        );
        chartVerifiedColor.value = toHexColor(
            response.data.chart_verified_color || "#3E8E41"
        );
        textColor.value = toHexColor(response.data.text_color || "#333333");
        textFont.value = response.data.text_font || "Figtree";
        console.debug("Preferencias cargadas:", response.data);
    } catch (error) {
        console.error("Error cargando preferencias:", error);
        toast.error($t("error_loading_preferences"), { timeout: 3000 });
    }
};

// Guardar preferencias
const savePreferences = async () => {
    try {
        await axios.post(
            "/skyfall/user/preferences",
            {
                card_bg_color: toHexColor(cardBgColor.value),
                chart_proposed_color: toHexColor(chartProposedColor.value),
                chart_verified_color: toHexColor(chartVerifiedColor.value),
                text_color: toHexColor(textColor.value),
                text_font: textFont.value,
            },
            { withCredentials: true }
        );
        console.debug("Preferencias guardadas");
        toast.success($t("preferences_saved"), { timeout: 3000 });
    } catch (error) {
        console.error("Error guardando preferencias:", error);
        toast.error($t("error_saving_preferences"), { timeout: 3000 });
    }
};

// Datos para los gráficos
const belongings = computed(() => props.resumen.data || []);
const areas = computed(() => {
    const areasData = globalStore.areasByBelonging(selectedBelongingId.value) || [];
    console.debug('Computed areas:', { belongingId: selectedBelongingId.value, areas: areasData });
    return areasData;
});

// Calcular alturas dinámicas
const getChartHeights = (data) => {
    const maxValue = Math.max(
        Math.max(
            ...(selectedBelongingId.value ? areas.value : belongings.value).map(
                (d) => d.total_propuestos || 0
            )
        ),
        Math.max(
            ...(selectedBelongingId.value ? areas.value : belongings.value).map(
                (d) => d.total_verificados || 0
            )
        ),
        100
    );
    const proposedHeight = Math.min(
        (data.total_propuestos / maxValue) * 98,
        98
    );
    const verifiedHeight = Math.min(
        (data.total_verificados / maxValue) * 98,
        98
    );
    return { proposedHeight, verifiedHeight };
};


// Manejar clic en gráfico
const selectBelonging = async (belongingId) => {
    selectedBelongingId.value = belongingId;
    try {
        const areas = await globalStore.fetchAreas(belongingId);
        console.debug('Áreas cargadas para belonging:', { belongingId, areas });
        if (!areas || areas.length === 0) {
            toast.error($t('no_areas_available'), { timeout: 3000 });
        }
    } catch (error) {
        console.error('Error al cargar áreas:', error);
        toast.error($t('areas_load_error'), { timeout: 3000 });
    }
};

// Manejar clic en área
const handleAreaClick = (belongingId, areaName) => {
    console.debug('handleAreaClick:', { belongingId, areaName });
    if (!areaName || typeof areaName !== 'string' || !['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'].includes(areaName)) {
        console.error('Nombre de área inválido:', areaName);
        toast.error($t('invalid_area_error'), { timeout: 3000 });
        return;
    }
    router.visit(
        route('skyfall.belonging-area-records.index', {
            belonging_id: Number(belongingId),
            area_name: areaName,
        }),
        {
            onError: (errors) => {
                console.error('Error al navegar:', errors);
                toast.error($t('navigation_error'), { timeout: 3000 });
            },
        }
    );
};

// Volver a belongings
const goBackToBelongings = () => {
    selectedBelongingId.value = null;
    globalStore.clearAreas();
    console.debug("Volviendo a belongings");
};

// Toggle menú
const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

// Scroll con flechas
const scrollLeft = () => {
    if (scrollContainer.value) {
        const itemWidth = 140;
        scrollContainer.value.scrollBy({
            left: -itemWidth,
            behavior: "smooth",
        });
    }
};

const scrollRight = () => {
    if (scrollContainer.value) {
        const itemWidth = 140;
        scrollContainer.value.scrollBy({ left: itemWidth, behavior: "smooth" });
    }
};

// Actualizar estilos
watch(
    [cardBgColor, chartProposedColor, chartVerifiedColor, textColor, textFont], () => {} );
</script>

<template>
    <AppLayout
        :title="globalStore.user.name ? globalStore.user.name + ' - ' + $t('Area Global') : $t('Area Global')">
        <div
            class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <HeaderSection
                :title="$t('Area Global')"
                :show-back-button="true"
            />
            <!-- Card principal -->
            <div
                class="rounded-2xl shadow-lg p-6 max-w-2xl mx-auto min-h-[400px] relative overflow-y-hidden mt-16"
                :style="{ backgroundColor: cardBgColor }"
            >
                <!-- Botón de menú (tres puntos) -->
                <button
                    @click="toggleMenu"
                    class="absolute top-2 right-2 p-3 text-neutral-1 dark:text-neutral-0 hover:text-main-1 dark:hover:text-main-1 z-30"
                >
                    <svg
                        class="w-6 h-6"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                    </svg>
                </button>

                <!-- Botón volver (en áreas) -->
                <button
                    v-if="selectedBelongingId"
                    @click="goBackToBelongings"
                    class="absolute top-4 left-4 p-2 flex items-center gap-2"
                    :style="{ color: chartProposedColor }"
                    @mouseover="$event.target.style.color = chartVerifiedColor"
                    @mouseout="$event.target.style.color = chartProposedColor"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>

                <!-- Menú lateral -->
                <div
                    v-if="isMenuOpen"
                    class="fixed top-0 right-0 h-full w-64 bg-neutral-0 dark:bg-neutral-2 shadow-lg z-50 p-4 transform transition-transform"
                    :class="{
                        'translate-x-0': isMenuOpen,
                        'translate-x-full': !isMenuOpen,
                    }"
                >
                    <button
                        @click="toggleMenu"
                        class="mb-4 text-main-1 dark:text-main-1"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <h2
                        class="text-lg font-bold text-neutral-1 dark:text-neutral-0 mb-4"
                    >
                        {{ $t("Styles") }}
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-base text-neutral-1 dark:text-neutral-0 mb-1"
                                >{{ $t("Background color") }}</label
                            >
                            <input
                                v-model="cardBgColor"
                                type="color"
                                class="w-full h-8 rounded"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-base text-neutral-1 dark:text-neutral-0 mb-1"
                                >{{ $t("proposed") }}</label
                            >
                            <input
                                v-model="chartProposedColor"
                                type="color"
                                class="w-full h-8 rounded"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-base text-neutral-1 dark:text-neutral-0 mb-1"
                                >{{ $t("verified") }}</label
                            >
                            <input
                                v-model="chartVerifiedColor"
                                type="color"
                                class="w-full h-8 rounded"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-base text-neutral-1 dark:text-neutral-0 mb-1"
                                >{{ $t("Text color") }}</label
                            >
                            <input
                                v-model="textColor"
                                type="color"
                                class="w-full h-8 rounded"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-base text-neutral-1 dark:text-neutral-0 mb-1"
                                >{{ $t("Typography") }}</label
                            >
                            <select
                                v-model="textFont"
                                class="w-full p-2 border rounded text-base"
                            >
                                <option
                                    v-for="option in fontOptions"
                                    :value="option.value"
                                    :key="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <button
                            @click="savePreferences"
                            class="w-full p-2 bg-main-0 text-neutral-0 rounded hover:bg-main-1 text-base"
                        >
                            {{ $t("Apply") }}
                        </button>
                    </div>
                </div>

                <div class="flex flex-row gap-6">
                    <!-- Izquierda: Foto, nombre, total -->
                    <div class="flex-shrink-0 flex flex-col items-center w-1/4">
                        <h2
                            class="text-lg font-bold"
                            :style="{ color: textColor, fontFamily: textFont }"
                        >
                            {{ globalStore.user.name }}
                        </h2>
                        <img
                            :src="globalStore.user.profile_photo_url"
                            alt="User profile"
                            class="w-24 h-24 rounded-full object-cover my-2"
                        />
                        <p
                            class="text-base"
                            :style="{ color: textColor, fontFamily: textFont }"
                        >
                            {{ $t("Total") }}:
                            {{ formatNumber(globalStore.sumas.totales) }}
                        </p>
                    </div>

                    <!-- Derecha: Gráficos y textos -->
                    <div class="flex-1 relative">
                        <!-- Textos -->
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <div
                                class="text-base text-neutral-2 dark:text-neutral-0"
                            >
                                {{ $t("chart_label_1") }}
                            </div>
                            <div
                                class="text-base text-neutral-2 dark:text-neutral-0"
                            >
                                {{ $t("chart_label_2") }}
                            </div>
                            <div
                                class="text-base text-neutral-2 dark:text-neutral-0"
                            >
                                {{ $t("chart_label_3") }}
                            </div>
                            <div
                                class="text-base text-neutral-2 dark:text-neutral-0"
                            >
                                {{ $t("chart_label_4") }}
                            </div>
                        </div>

                        <!-- Gráficos con flechas -->
                        <div class="relative max-w-[360px] mx-auto">
                            <!-- Textos Propuestos y Verificados -->
                            <div
                                class="absolute left-[-90px] min-w-[80px] text-base z-30"
                                :style="{
                                    color: textColor,
                                    fontFamily: textFont,
                                    whiteSpace: 'normal',
                                    backgroundColor: 'rgba(255, 0, 0, 0.1)',
                                }"
                            >
                                <p class="absolute top-[220px]">
                                    {{ $t("proposed") }}:
                                </p>
                                <p class="absolute top-[240px]">
                                    {{ $t("verified") }}:
                                </p>
                            </div>
                            <!-- Flecha izquierda -->
                            <button
                                @click="scrollLeft"
                                class="absolute left-[-8px] top-1/2 transform -translate-y-1/2 p-2 z-20"
                                :style="{ color: chartProposedColor }"
                            >
                                <svg
                                    class="w-8 h-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>
                            </button>
                            <!-- Flecha derecha -->
                            <button
                                @click="scrollRight"
                                class="absolute right-0 top-1/2 transform -translate-y-1/2 p-2 z-20"
                                :style="{ color: chartVerifiedColor }"
                            >
                                <svg
                                    class="w-8 h-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </button>
                            <!-- Contenedor de gráficos -->
                            <div
                                ref="scrollContainer"
                                class="flex snap-x snap-mandatory overflow-x-auto overflow-y-hidden scrollbar-hidden max-w-[360px]"
                                :style="{ backgroundColor: cardBgColor }"
                            >
                                <div
                                    v-for="(item, index) in (selectedBelongingId ? areas : belongings)"
                                    :key="selectedBelongingId ? item.name : item.belonging_id"
                                    class="flex-shrink-0 snap-center w-[140px] mx-1"
                                    @mouseover="console.debug('Item en v-for:', { index, item })"
                                >
                                    <div class="text-center relative">
                                        <div
                                            class="h-[260px] w-full relative flex items-center justify-center"
                                        >
                                            <Bar3D
                                                :area-name="selectedBelongingId ? item.name : ''"
                                                :proposed-height="selectedBelongingId ? item.total_propuestos / 1000 : item.total_propuestos / 1000"
                                                :verified-height="selectedBelongingId ? item.total_verificados / 1000 : item.total_verificados / 1000"
                                                :aria-label="selectedBelongingId ? $t('area_chart', { name: item.area_display_name }) : $t('belonging_chart', { name: item.belonging_name })"
                                                @click="selectedBelongingId ? handleAreaClick(selectedBelongingId, item.name) : selectBelonging(item.belonging_id)"
                                            />
                                            <!-- Nombre -->
                                            <div
                                                class="absolute top-[200px] w-full text-center text-[14px]"
                                                :style="{
                                                    color: textColor,
                                                    fontFamily: textFont,
                                                }"
                                            >
                                                {{
                                                    selectedBelongingId
                                                        ? item.area_display_name || $t('unknown_area')
                                                        : item.belonging_name || $t('no_belonging')
                                                }}
                                            </div>
                                            <!-- Valor propuestos -->
                                            <div
                                                class="absolute top-[220px] w-full text-center text-[14px]"
                                                :style="{
                                                    color: chartProposedColor,
                                                    fontFamily: textFont,
                                                }"
                                            >
                                                {{
                                                    formatNumber(
                                                        item.total_propuestos
                                                    )
                                                }}
                                            </div>
                                            <!-- Valor verificados -->
                                            <div
                                                class="absolute top-[240px] w-full text-center text-[14px]"
                                                :style="{
                                                    color: chartVerifiedColor,
                                                    fontFamily: textFont,
                                                }"
                                            >
                                                {{
                                                    formatNumber(
                                                        item.total_verificados
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:root {
    --card-bg: v-bind(cardBgColor);
    --font-family: v-bind(textFont);
}

.card {
    background-color: var(--card-bg);
    font-family: var(--font-family);
}

.scrollbar-hidden::-webkit-scrollbar {
    display: none;
}
.scrollbar-hidden {
    scrollbar-width: none;
}
.snap-x {
    scroll-snap-type: x mandatory;
}
.snap-center {
    scroll-snap-align: center;
}

@media (max-width: 1024px) {
    .max-w-\[360px\] {
        max-width: 300px;
    }
    .w-\[140px\] {
        width: 120px;
    }
    .h-\[260px\] {
        height: 220px;
    }
    .w-16 {
        width: 48px;
        height: 48px;
    }
    .text-lg {
        font-size: 1rem;
    }
    .text-base {
        font-size: 0.875rem;
    }
    .text-\[14px\] {
        font-size: 12px;
    }
    .min-h-\[400px\] {
        min-height: 320px;
    }
    .mt-24 {
        margin-top: 5rem;
    }
    .top-\[200px\] {
        top: 160px;
    }
    .top-\[220px\] {
        top: 180px;
    }
    .top-\[240px\] {
        top: 200px;
    }
    .left-\[-8px\] {
        left: -6px;
    }
    .left-\[-90px\] {
        left: -70px;
    }
}
@media (max-width: 640px) {
    .flex-row {
        flex-direction: row;
    }
    .w-1\/4 {
        width: 30%;
    }
    .max-w-\[360px\] {
        max-width: 240px;
    }
    .w-\[140px\] {
        width: 100px;
    }
    .h-\[260px\] {
        height: 180px;
    }
    .w-16 {
        width: 40px;
        height: 40px;
    }
    .text-lg {
        font-size: 0.875rem;
    }
    .text-base {
        font-size: 0.75rem;
    }
    .text-\[14px\] {
        font-size: 10px;
    }
    .min-h-\[400px\] {
        min-height: 280px;
    }
    .mt-24 {
        margin-top: 3.5rem;
    }
    .top-\[200px\] {
        top: 120px;
    }
    .top-\[220px\] {
        top: 140px;
    }
    .top-\[240px\] {
        top: 160px;
    }
    .left-\[-8px\] {
        left: -4px;
    }
    .left-\[-90px\] {
        left: -50px;
    }
}
</style>