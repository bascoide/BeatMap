// ============================================
// INICIALIZAÇÃO DO MAPA
// ============================================

// Inicializar o mapa
const map = L.map("map").setView([20, 0], 2);
// ============================================
// ELEMENTOS DOM
// ============================================

const cityInput = document.getElementById("cityInput");
const resultsContainer = document.getElementById("resultsContainer");
const clearSearchBtn = document.getElementById("clearSearchBtn");
const searchHelper = document.getElementById("searchHelper");
const searchModeToggle = document.getElementById("searchModeToggle");
const searchModeSwitcher = document.getElementById("searchModeSwitcher");
const infoBox = document.getElementById("sidebarInfo");
const cityName = document.getElementById("cityName");
const cityDetails = document.getElementById("cityDetails");
const cityLat = document.getElementById("cityLat");
const cityLng = document.getElementById("cityLng");
const closeBtn = document.getElementById("closeSidebarInfo");
const locateBtn = document.getElementById("locateBtn");
const randomBtn = document.getElementById("randomBtn");
const resetBtn = document.getElementById("resetBtn");
const legendToggle = document.getElementById("legendToggle");
const legendDock = document.getElementById("legendDock");
const municipiosLegend = document.getElementById("municipiosLegend");
const loading = document.getElementById("loading");
const menuToggle = document.getElementById("menuToggle");
const sidebar = document.getElementById("sidebar");
const cityImageContainer = document.getElementById("cityImageContainer");
const cityImage = document.getElementById("cityImage");
const councilArtistsSection = document.getElementById("councilArtistsSection");
const artistsList = document.getElementById("artistsList");
const artistsFilters = document.getElementById("artistsFilters");
const artistSortInput = document.getElementById("artistSortInput");
const artistGenreField = document.getElementById("artistGenreField");
const artistGenreInput = document.getElementById("artistGenreInput");
const artistGenreFilterGroup = artistGenreInput?.closest(
  ".artist-filter-group",
);
const artistGenreClearBtn = document.getElementById("artistGenreClearBtn");
const artistGenreDropdown = document.getElementById("artistGenreDropdown");
const artistGenreDropdownList = document.getElementById(
  "artistGenreDropdownList",
);
const artistGenreDropdownEmpty = document.getElementById(
  "artistGenreDropdownEmpty",
);
const artistDetailsPanel = document.getElementById("artistDetailsPanel");
const backToCouncilBtn = document.getElementById("backToCouncilBtn");
const artistModal = document.getElementById("artistModal");
const artistModalOverlay = document.getElementById("artistModalOverlay");
const artistModalClose = document.getElementById("artistModalClose");
const artistModalBody = document.getElementById("artistModalBody");
const artistModalCard = artistModal?.querySelector(".artist-modal-card");
const globalChatModal = document.getElementById("globalChatModal");
const globalChatMessages = document.getElementById("globalChatMessages");
const globalChatPendingNotice = document.getElementById(
  "globalChatPendingNotice",
);
const globalChatForm = document.getElementById("globalChatForm");
const globalChatInput = document.getElementById("globalChatInput");
const globalChatCounter = document.getElementById("globalChatCounter");
const globalChatMinimizeBtn = document.getElementById("globalChatMinimizeBtn");
const globalChatEmojiBtn = document.getElementById("globalChatEmojiBtn");
const globalChatEmojiPicker = document.getElementById("globalChatEmojiPicker");
const ARTIST_MODAL_ANIMATION_MS = 220;
let artistModalCloseTimeout = null;
const userInfo = document.getElementById("userInfo");
const accountMenu = document.getElementById("accountMenu");
const accountBtn = document.getElementById("accountBtn");
const accountIcon = document.getElementById("accountIcon");
const accountDropdown = document.getElementById("accountDropdown");
const privateInbox = document.getElementById("privateInbox");
const privateInboxBtn = document.getElementById("privateInboxBtn");
const privateInboxBadge = document.getElementById("privateInboxBadge");
const privateInboxDropdown = document.getElementById("privateInboxDropdown");
const privateInboxHeader = document.getElementById("privateInboxHeader");
const privateInboxList = document.getElementById("privateInboxList");
const layerSwitcher = document.getElementById("layerSwitcher");
const onboardingOverlay = document.getElementById("onboardingOverlay");
const onboardingHighlight = document.getElementById("onboardingHighlight");
const onboardingTooltip = document.getElementById("onboardingTooltip");
const onboardingStepCounter = document.getElementById("onboardingStepCounter");
const onboardingTitle = document.getElementById("onboardingTitle");
const onboardingDescription = document.getElementById("onboardingDescription");
const onboardingSkipBtn = document.getElementById("onboardingSkipBtn");
const onboardingPrevBtn = document.getElementById("onboardingPrevBtn");
const onboardingNextBtn = document.getElementById("onboardingNextBtn");
const CHAT_PICKER_EMOJIS = [
  "😀",
  "😄",
  "😂",
  "😉",
  "😍",
  "🥳",
  "🤘",
  "🔥",
  "🎧",
  "🎤",
  "🎸",
  "🎹",
  "🥁",
  "🎶",
  "🎵",
  "✨",
  "👏",
  "🙏",
  "🤝",
  "❤️",
  "💯",
  "📍",
  "🚀",
  "🙌",
];

function setLegendVisibility(visible) {
  if (!legendToggle || !municipiosLegend) return;

  municipiosLegend.classList.toggle("show", visible);
  legendToggle.classList.toggle("active", !visible);
  legendToggle.setAttribute("aria-expanded", visible ? "true" : "false");
  legendToggle.setAttribute(
    "aria-label",
    visible ? "Ocultar legenda" : "Mostrar legenda",
  );

  followGlobalChatLegendTransition();
}
// ============================================
// VARIÁVEIS GLOBAIS
// ============================================

let currentMarker = null;
let currentCityBoundary = null;
let infoBoxVisible = true;
let portugalGeoJSON = null;
let portugalLayer = null;
let outsideMask = null;
let lastBoundarySource = null;
let municipiosLayer = null;
let municipioSelecionado = null;
let municipiosData = null;
let distritoSelecionado = null;
let municipiosLoadPromise = null;
let randomSelectionInProgress = false;

// Configurações do Nominatim
const NOMINATIM_URL = "https://nominatim.openstreetmap.org";
let lastRequestTime = 0;

let distritosLayer = null;
let distritosDataCache = null;
let distritosLoadPromise = null;
let currentLayerType = "municipios";
let isInitialBootPhase = true;
let artistCoverageLoadPromise = null;
let artistCouncilsSet = null;
let artistDistrictsSet = null;
let searchTimeout;
let activeSearchResultIndex = -1;
let genreSearchIndex = null;
let genreSearchIndexPromise = null;
let selectedGenres = new Set();
let currentArtistsById = new Map();
let currentArtistsRaw = [];
let currentArtistSort = "aleatorio";
let selfMapFocusSortResetPending = false;
let selfMapFocusCouncilName = "";
let currentArtistGenreFilter = "";
let artistGenreOptionsList = [];
let currentSessionUserType = null;
let currentSessionAccountId = 0;
let currentSessionUsername = "";
let currentSessionEmail = "";
let currentSessionArtistIsPending = false;
let globalChatPollingTimer = null;
let globalChatLastMessageId = 0;
let globalChatKnownMessageIds = new Set();
let globalChatLoading = false;
let globalChatInitialized = false;
let globalChatMinimized = false;
let globalChatLegendFollowRaf = null;
let privateInboxPollingTimer = null;
let privateInboxLoading = false;
let privateInboxBadgePollingTimer = null;
let privateInboxBadgeLoading = false;
let privateInboxUnreadCount = 0;
let privateInboxViewMode = "conversations";
let currentPrivateConversationArtistId = 0;
let currentPrivateConversationArtistName = "";
let currentPrivateConversationArtistAvatar = "";
let currentPrivateConversationLastMessageId = 0;
let privateConversationDraftByArtistId = new Map();
let privateConversationPendingAudioByArtistId = new Map();
let privateConversationAudioRecorder = null;
let privateConversationAudioRecorderStream = null;
let privateConversationAudioRecorderChunks = [];
let privateConversationAudioRecorderArtistId = 0;
let privateConversationAudioRecorderStartedAt = 0;
let privateConversationAudioRecorderMimeType = "";
let privateConversationAudioRecorderPersistOnStop = true;
let privateConversationAudioRecorderLimitReached = false;
let privateConversationAudioRecorderStopResolver = null;
let privateConversationAudioRecorderInterval = null;
const PRIVATE_INBOX_POLL_INTERVAL_MS = 3000;
const PRIVATE_INBOX_BADGE_POLL_INTERVAL_MS = 4000;
const PRIVATE_AUDIO_MAX_BYTES = 10 * 1024 * 1024;
const PRIVATE_VOICE_MAX_DURATION_SECONDS = 60;
const GLOBAL_CHAT_MINIMIZED_KEY = "beatmap_global_chat_minimized";
const layerToggle = document.getElementById("layerToggle");
const ONBOARDING_HIGHLIGHT_PADDING = 10;
let onboardingSteps = [];
let onboardingCurrentIndex = 0;
let onboardingActive = false;
let onboardingAutoCouncilSelected = false;
let onboardingCouncilSelectionInProgress = false;
let onboardingRenderToken = 0;
let onboardingPreparedStepId = null;
let onboardingVisibilityRetryStepId = null;
let onboardingVisibilityRetryCount = 0;
let onboardingVisibilityRetryTimer = null;

// URL para o GeoJSON dos Distritos (Exemplo: podes usar o do GitHub do dssg-pt ou outro)
// Se tiveres um ficheiro local, muda para 'dados/distritos.geojson'
const DISTRICTS_GEOJSON_URL = "data/distritos_pt.geojson";

// Zoom mínimo para mostrar nomes dos municípios/distritos
const MIN_ZOOM_FOR_LABELS = 10;

// ============================================
// OBSERVERS (Observadores de Mutação)
// ============================================

// Observador para a classe da sidebar, para aplicar efeitos no mapa
const sidebarObserver = new MutationObserver((mutationsList) => {
  for (const mutation of mutationsList) {
    if (mutation.type === "attributes" && mutation.attributeName === "class") {
      // Quando a sidebar ganha/perde a classe 'open', adiciona/remove uma classe no body
      if (sidebar.classList.contains("open")) {
        document.body.classList.add("sidebar-is-open");
      } else {
        document.body.classList.remove("sidebar-is-open");
      }
    }
  }
});

sidebarObserver.observe(sidebar, { attributes: true });

// ============================================
// FUNÇÕES UTILITÁRIAS
// ============================================

// Função para controlar requisições ao Nominatim
async function makeNominatimRequest(url) {
  const now = Date.now();
  const timeSinceLastRequest = now - lastRequestTime;

  if (timeSinceLastRequest < 1000) {
    await new Promise((resolve) =>
      setTimeout(resolve, 1000 - timeSinceLastRequest),
    );
  }

  lastRequestTime = Date.now();

  const response = await fetch(url, {
    headers: {
      "User-Agent": "PortugalCityExplorer/1.0 (seusite@email.com)",
    },
  });

  return response;
}

// Função para buscar e mostrar imagem da cidade (Wikipedia)
async function fetchCityImage(cityName) {
  // Resetar estado da imagem
  cityImage.src = "";
  cityImage.style.opacity = "0";
  cityImageContainer.style.display = "none";

  const endpoint = `https://pt.wikipedia.org/w/api.php?action=query&titles=${encodeURIComponent(
    cityName,
  )}&prop=pageimages&format=json&pithumbsize=400&origin=*`;

  try {
    const response = await fetch(endpoint);
    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    const data = await response.json();
    const pages = data.query.pages;
    const pageId = Object.keys(pages)[0];

    if (pageId !== "-1" && pages[pageId].thumbnail) {
      const imageUrl = pages[pageId].thumbnail.source;
      cityImage.src = imageUrl;
      cityImage.onload = () => {
        cityImageContainer.style.display = "block";
        cityImage.style.opacity = "1";
      };
    } else {
      console.log(`Nenhuma imagem encontrada para ${cityName} na Wikipedia.`);
    }
  } catch (error) {
    console.error("Erro ao buscar imagem da cidade:", error);
  }
}

// Função para mostrar/ocultar loading
function showLoading(show) {
  if (isInitialBootPhase) {
    loading.style.display = "none";
    return;
  }

  loading.style.display = show ? "flex" : "none";
}

// Função para mostrar mensagem temporária
function showTempMessage(message, type = "info", duration = 3000) {
  const existingMsg = document.querySelector(".temp-message");
  if (existingMsg) {
    existingMsg.remove();
  }

  const msgDiv = document.createElement("div");
  msgDiv.className = "temp-message";
  msgDiv.textContent = message;

  if (type === "warning") {
    msgDiv.style.backgroundColor = "rgba(255, 193, 7, 0.9)";
    msgDiv.style.color = "#856404";
  } else if (type === "error") {
    msgDiv.style.backgroundColor = "rgba(220, 53, 69, 0.9)";
    msgDiv.style.color = "#721c24";
  } else if (type === "success") {
    msgDiv.style.backgroundColor = "rgba(40, 167, 69, 0.9)";
    msgDiv.style.color = "#155724";
  } else {
    msgDiv.style.backgroundColor = "rgba(23, 162, 184, 0.9)";
    msgDiv.style.color = "#0c5460";
  }

  document.querySelector(".map-container").appendChild(msgDiv);
  msgDiv.style.display = "block";

  setTimeout(() => {
    msgDiv.style.opacity = "0";
    msgDiv.style.transition = "opacity 0.5s ease";
    setTimeout(() => {
      if (msgDiv.parentNode) {
        msgDiv.parentNode.removeChild(msgDiv);
      }
    }, 500);
  }, duration);
}

function isGlobalChatVisible() {
  return !!globalChatModal && !globalChatModal.hidden;
}

function clearGlobalChatPolling() {
  if (globalChatPollingTimer) {
    clearInterval(globalChatPollingTimer);
    globalChatPollingTimer = null;
  }
}

function updateGlobalChatPositionNearLegend() {
  if (!globalChatModal || !legendDock) {
    return;
  }

  const isCompactViewport = window.innerWidth <= 900;
  if (isCompactViewport) {
    globalChatModal.style.right = "8px";
    return;
  }

  const dockWidth = Math.max(
    0,
    Math.ceil(legendDock.getBoundingClientRect().width),
  );
  const spacing = 10;
  globalChatModal.style.right = `${20 + dockWidth + spacing}px`;
}

function followGlobalChatLegendTransition(durationMs = 420) {
  if (!globalChatModal || !legendDock) {
    return;
  }

  if (globalChatLegendFollowRaf) {
    cancelAnimationFrame(globalChatLegendFollowRaf);
    globalChatLegendFollowRaf = null;
  }

  const startedAt = performance.now();

  const step = (now) => {
    updateGlobalChatPositionNearLegend();

    if (now - startedAt < durationMs) {
      globalChatLegendFollowRaf = requestAnimationFrame(step);
      return;
    }

    globalChatLegendFollowRaf = null;
    updateGlobalChatPositionNearLegend();
  };

  globalChatLegendFollowRaf = requestAnimationFrame(step);
}

function applyGlobalChatMinimizeState() {
  if (!globalChatModal || !globalChatMinimizeBtn) {
    return;
  }

  globalChatModal.classList.toggle("is-minimized", globalChatMinimized);
  globalChatMinimizeBtn.setAttribute(
    "aria-label",
    globalChatMinimized ? "Expandir chat" : "Minimizar chat",
  );
  globalChatMinimizeBtn.setAttribute(
    "aria-expanded",
    globalChatMinimized ? "false" : "true",
  );
  globalChatMinimizeBtn.textContent = globalChatMinimized ? "⌄" : "⌃";
}

function restoreGlobalChatMinimizeState() {
  const raw = localStorage.getItem(GLOBAL_CHAT_MINIMIZED_KEY);
  globalChatMinimized = raw === "1";
  applyGlobalChatMinimizeState();
}

function toggleGlobalChatMinimized() {
  if (currentSessionUserType === "artist" && currentSessionArtistIsPending) {
    return;
  }

  globalChatMinimized = !globalChatMinimized;
  localStorage.setItem(
    GLOBAL_CHAT_MINIMIZED_KEY,
    globalChatMinimized ? "1" : "0",
  );
  applyGlobalChatMinimizeState();
}

function startGlobalChatPolling() {
  if (currentSessionUserType === "artist" && currentSessionArtistIsPending) {
    clearGlobalChatPolling();
    return;
  }

  clearGlobalChatPolling();

  globalChatPollingTimer = setInterval(async () => {
    if (!isGlobalChatVisible()) {
      clearGlobalChatPolling();
      return;
    }

    await fetchGlobalChatMessages({ incremental: true, silent: true });
  }, 8000);
}

function updateGlobalChatCounter() {
  if (!globalChatCounter || !globalChatInput) {
    return;
  }

  const currentLength = (globalChatInput.value || "").length;
  globalChatCounter.textContent = `${currentLength}/255`;
}

function autoResizeGlobalChatInput() {
  if (!globalChatInput) {
    return;
  }

  const computedStyles = window.getComputedStyle(globalChatInput);
  const minHeight = parseFloat(computedStyles.minHeight) || 52;
  const maxHeight = parseFloat(computedStyles.maxHeight) || 150;

  globalChatInput.style.height = "auto";
  const targetHeight = Math.min(
    maxHeight,
    Math.max(minHeight, globalChatInput.scrollHeight),
  );
  globalChatInput.style.height = `${targetHeight}px`;
  globalChatInput.style.overflowY =
    globalChatInput.scrollHeight > maxHeight ? "auto" : "hidden";
}

function autoResizePrivateConversationInput(textarea) {
  if (!(textarea instanceof HTMLTextAreaElement)) {
    return;
  }

  const computedStyles = window.getComputedStyle(textarea);
  const minHeight = parseFloat(computedStyles.minHeight) || 34;
  const maxHeight = parseFloat(computedStyles.maxHeight) || 120;

  textarea.style.height = "auto";
  const targetHeight = Math.min(
    maxHeight,
    Math.max(minHeight, textarea.scrollHeight),
  );
  textarea.style.height = `${targetHeight}px`;
  textarea.style.overflowY =
    textarea.scrollHeight > maxHeight ? "auto" : "hidden";
}

function formatBytes(bytes) {
  const normalizedBytes = Number(bytes) || 0;
  if (normalizedBytes <= 0) {
    return "0 B";
  }

  const units = ["B", "KB", "MB", "GB"];
  const unitIndex = Math.min(
    units.length - 1,
    Math.floor(Math.log(normalizedBytes) / Math.log(1024)),
  );
  const value = normalizedBytes / 1024 ** unitIndex;
  const digits = unitIndex === 0 ? 0 : value >= 10 ? 1 : 2;
  return `${value.toFixed(digits)} ${units[unitIndex]}`;
}

function formatDurationSeconds(value) {
  const totalSeconds = Math.max(0, Math.round(Number(value) || 0));
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds % 60;
  return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
}

function buildPrivateAudioPlayerHtml({
  audioUrl = "",
  durationSeconds = null,
  compact = false,
  downloadName = "",
} = {}) {
  const normalizedDuration = Number.isFinite(Number(durationSeconds))
    ? Math.max(0, Number(durationSeconds))
    : 0;
  const safeDownloadName = escapeHtml(downloadName || "audio");

  return `
    <div class="private-audio-player ${compact ? "is-compact" : ""}" data-private-audio-player>
      <audio class="private-audio-element" preload="metadata" src="${escapeHtml(audioUrl || "")}"></audio>
      <button
        type="button"
        class="private-audio-toggle"
        data-private-audio-toggle
        aria-label="Reproduzir áudio"
      >
        <span data-private-audio-toggle-icon>▶</span>
      </button>
      <div class="private-audio-progress-wrap">
        <input
          type="range"
          class="private-audio-progress"
          data-private-audio-progress
          min="0"
          max="${normalizedDuration > 0 ? escapeHtml(String(normalizedDuration)) : "100"}"
          step="0.01"
          value="0"
          aria-label="Navegar no áudio"
        >
        <div class="private-audio-times">
          <span data-private-audio-current>00:00</span>
          <span data-private-audio-duration>${escapeHtml(formatDurationSeconds(normalizedDuration))}</span>
        </div>
      </div>
      <a
        class="private-audio-download"
        href="${escapeHtml(audioUrl || "")}"
        download="${safeDownloadName}"
        aria-label="Descarregar áudio"
        title="Descarregar áudio"
      >
        <span class="private-audio-download-icon" aria-hidden="true">↓</span>
      </a>
    </div>
  `;
}

function syncPrivateAudioPlayerUi(player) {
  if (!(player instanceof HTMLElement)) {
    return;
  }

  const audio = player.querySelector(".private-audio-element");
  const progress = player.querySelector("[data-private-audio-progress]");
  const current = player.querySelector("[data-private-audio-current]");
  const duration = player.querySelector("[data-private-audio-duration]");
  const toggleIcon = player.querySelector("[data-private-audio-toggle-icon]");
  if (!(audio instanceof HTMLAudioElement)) {
    return;
  }

  const safeDuration = Number.isFinite(audio.duration) && audio.duration > 0
    ? audio.duration
    : Number(progress instanceof HTMLInputElement ? progress.max : 0) || 0;
  const safeCurrent = Number.isFinite(audio.currentTime) ? audio.currentTime : 0;

  if (progress instanceof HTMLInputElement) {
    const max = safeDuration > 0 ? safeDuration : 100;
    const value = Math.min(safeCurrent, max);
    progress.max = String(max);
    progress.value = String(value);
    progress.style.setProperty(
      "--private-audio-progress",
      `${max > 0 ? (value / max) * 100 : 0}%`,
    );
  }

  if (current instanceof HTMLElement) {
    current.textContent = formatDurationSeconds(safeCurrent);
  }

  if (duration instanceof HTMLElement) {
    duration.textContent = formatDurationSeconds(safeDuration);
  }

  if (toggleIcon instanceof HTMLElement) {
    toggleIcon.textContent = audio.paused ? "▶" : "❚❚";
  }
}

function pauseOtherPrivateAudioPlayers(activeAudio) {
  if (!(activeAudio instanceof HTMLAudioElement)) {
    return;
  }

  document.querySelectorAll(".private-audio-element").forEach((node) => {
    if (!(node instanceof HTMLAudioElement) || node === activeAudio) {
      return;
    }

    node.pause();
    const player = node.closest("[data-private-audio-player]");
    if (player instanceof HTMLElement) {
      syncPrivateAudioPlayerUi(player);
    }
  });
}

function initializePrivateAudioPlayer(player) {
  if (!(player instanceof HTMLElement) || player.dataset.privateAudioReady === "1") {
    return;
  }

  const audio = player.querySelector(".private-audio-element");
  const toggle = player.querySelector("[data-private-audio-toggle]");
  const progress = player.querySelector("[data-private-audio-progress]");
  if (!(audio instanceof HTMLAudioElement)) {
    return;
  }

  const sync = () => syncPrivateAudioPlayerUi(player);

  audio.addEventListener("loadedmetadata", sync);
  audio.addEventListener("timeupdate", sync);
  audio.addEventListener("pause", sync);
  audio.addEventListener("ended", sync);
  audio.addEventListener("play", () => {
    pauseOtherPrivateAudioPlayers(audio);
    sync();
  });

  if (toggle instanceof HTMLButtonElement) {
    toggle.addEventListener("click", async () => {
      try {
        if (audio.paused) {
          pauseOtherPrivateAudioPlayers(audio);
          await audio.play();
        } else {
          audio.pause();
        }
      } catch (error) {
        sync();
      }
    });
  }

  if (progress instanceof HTMLInputElement) {
    const seek = () => {
      const nextTime = Number(progress.value || 0);
      if (Number.isFinite(nextTime)) {
        audio.currentTime = nextTime;
      }
      sync();
    };

    progress.addEventListener("input", seek);
    progress.addEventListener("change", seek);
  }

  player.dataset.privateAudioReady = "1";
  sync();
}

function initializePrivateAudioPlayers(root = privateInboxList) {
  if (!(root instanceof Element)) {
    return;
  }

  root.querySelectorAll("[data-private-audio-player]").forEach((player) => {
    initializePrivateAudioPlayer(player);
  });
}

function isPrivateVoiceRecordingSupported() {
  return !!(
    window.MediaRecorder &&
    navigator.mediaDevices &&
    typeof navigator.mediaDevices.getUserMedia === "function"
  );
}

function getPrivateVoiceRecordingMimeType() {
  if (!(window.MediaRecorder && typeof MediaRecorder.isTypeSupported === "function")) {
    return "";
  }

  const candidates = [
    "audio/webm;codecs=opus",
    "audio/webm",
    "audio/ogg;codecs=opus",
    "audio/ogg",
    "audio/mp4",
  ];

  return candidates.find((candidate) => MediaRecorder.isTypeSupported(candidate)) || "";
}

function getPrivateConversationDraft(artistId) {
  if (!Number.isInteger(artistId) || artistId <= 0) {
    return "";
  }

  return (privateConversationDraftByArtistId.get(artistId) || "").toString();
}

function setPrivateConversationDraft(artistId, value) {
  if (!Number.isInteger(artistId) || artistId <= 0) {
    return;
  }

  const normalized = (value || "").toString();
  if (!normalized) {
    privateConversationDraftByArtistId.delete(artistId);
    return;
  }

  privateConversationDraftByArtistId.set(artistId, normalized);
}

function getPrivateConversationPendingAudio(artistId) {
  if (!Number.isInteger(artistId) || artistId <= 0) {
    return null;
  }

  return privateConversationPendingAudioByArtistId.get(artistId) || null;
}

function clearPrivateConversationPendingAudio(artistId) {
  if (!Number.isInteger(artistId) || artistId <= 0) {
    return;
  }

  const existing = privateConversationPendingAudioByArtistId.get(artistId);
  if (existing?.objectUrl) {
    URL.revokeObjectURL(existing.objectUrl);
  }

  privateConversationPendingAudioByArtistId.delete(artistId);
}

function setPrivateConversationPendingAudio(artistId, payload) {
  if (!Number.isInteger(artistId) || artistId <= 0 || !payload?.file || !payload?.objectUrl) {
    return;
  }

  clearPrivateConversationPendingAudio(artistId);
  privateConversationPendingAudioByArtistId.set(artistId, {
    file: payload.file,
    objectUrl: payload.objectUrl,
    durationSeconds: Number.isFinite(payload.durationSeconds)
      ? Number(payload.durationSeconds)
      : null,
    source: payload.source === "voice_recording" ? "voice_recording" : "audio_file",
    displayName: (payload.displayName || payload.file.name || "Áudio").toString(),
    sizeBytes: Number(payload.sizeBytes || payload.file.size || 0),
  });
}

function isRecordingPrivateConversationAudioForArtist(artistId) {
  return (
    Number.isInteger(artistId) &&
    artistId > 0 &&
    privateConversationAudioRecorderArtistId === artistId &&
    privateConversationAudioRecorder instanceof MediaRecorder &&
    privateConversationAudioRecorder.state !== "inactive"
  );
}

function getPrivateConversationRecordingElapsedSeconds() {
  if (privateConversationAudioRecorderStartedAt <= 0) {
    return 0;
  }

  return Math.min(
    PRIVATE_VOICE_MAX_DURATION_SECONDS,
    (Date.now() - privateConversationAudioRecorderStartedAt) / 1000,
  );
}

function clearPrivateConversationAudioRecorderInterval() {
  if (privateConversationAudioRecorderInterval) {
    clearInterval(privateConversationAudioRecorderInterval);
    privateConversationAudioRecorderInterval = null;
  }
}

function stopPrivateConversationAudioRecorderStream() {
  if (!privateConversationAudioRecorderStream) {
    return;
  }

  privateConversationAudioRecorderStream.getTracks().forEach((track) => {
    track.stop();
  });
  privateConversationAudioRecorderStream = null;
}

function buildPrivateConversationAudioHintText(artistId) {
  if (isRecordingPrivateConversationAudioForArtist(artistId)) {
    return `A gravar ${formatDurationSeconds(getPrivateConversationRecordingElapsedSeconds())} / ${formatDurationSeconds(PRIVATE_VOICE_MAX_DURATION_SECONDS)}`;
  }

  const pendingAudio = getPrivateConversationPendingAudio(artistId);
  if (pendingAudio) {
    const kindLabel = pendingAudio.source === "voice_recording"
      ? "Mensagem de voz pronta para enviar"
      : "Áudio pronto";
    const durationLabel = Number.isFinite(pendingAudio.durationSeconds)
      ? ` ${formatDurationSeconds(pendingAudio.durationSeconds)}`
      : "";
    const behaviorLabel = pendingAudio.source === "voice_recording"
      ? "Vai ser enviada sem texto."
      : "Podes escrever texto na mesma mensagem.";
    return `${kindLabel}.${durationLabel} ${behaviorLabel}`.trim();
  }

  return "Áudio até 10 MB. Voz até 1:00.";
}

function isPrivateConversationVoiceMessageMode(artistId) {
  return getPrivateConversationPendingAudio(artistId)?.source === "voice_recording";
}

function buildPrivateConversationAudioPreviewHtml(artistId) {
  if (isRecordingPrivateConversationAudioForArtist(artistId)) {
    return `
      <div class="private-conversation-audio-card is-recording">
        <div class="private-conversation-audio-card-top">
          <strong>A gravar mensagem de voz</strong>
          <span>${escapeHtml(formatDurationSeconds(getPrivateConversationRecordingElapsedSeconds()))}</span>
        </div>
        <div class="private-conversation-audio-wave" aria-hidden="true">
          <span></span><span></span><span></span>
        </div>
      </div>
    `;
  }

  const pendingAudio = getPrivateConversationPendingAudio(artistId);
  if (!pendingAudio) {
    return "";
  }

  const safeName = escapeHtml(pendingAudio.displayName || "Áudio");
  const safeUrl = escapeHtml(pendingAudio.objectUrl || "");
  const metaParts = [formatBytes(pendingAudio.sizeBytes)];
  if (Number.isFinite(pendingAudio.durationSeconds)) {
    metaParts.unshift(formatDurationSeconds(pendingAudio.durationSeconds));
  }

  return `
    <div class="private-conversation-audio-card">
      <div class="private-conversation-audio-card-top">
        <strong>${safeName}</strong>
        <button type="button" class="private-conversation-audio-remove" data-private-audio-remove>Remover</button>
      </div>
      <div class="private-conversation-audio-meta">${escapeHtml(metaParts.join(" • "))}</div>
      ${buildPrivateAudioPlayerHtml({
        audioUrl: safeUrl,
        durationSeconds: pendingAudio.durationSeconds,
        downloadName: pendingAudio.displayName || "audio",
      })}
    </div>
  `;
}

function refreshPrivateConversationComposerUi() {
  if (!privateInboxList) {
    return;
  }

  const form = privateInboxList.querySelector("form[data-private-conversation-form]");
  if (!(form instanceof HTMLFormElement)) {
    return;
  }

  const artistId = currentPrivateConversationArtistId;
  const isRecording = isRecordingPrivateConversationAudioForArtist(artistId);
  const preview = form.querySelector("[data-private-audio-preview]");
  const hint = form.querySelector("[data-private-audio-hint]");
  const recordButton = form.querySelector("[data-private-audio-record]");
  const attachButton = form.querySelector("[data-private-audio-trigger]");
  const fileInput = form.querySelector("[data-private-audio-input]");
  const textInput = form.querySelector("[data-private-conversation-input]");
  const isVoiceMessageMode = isPrivateConversationVoiceMessageMode(artistId);

  if (preview instanceof HTMLElement) {
    const previewHtml = buildPrivateConversationAudioPreviewHtml(artistId);
    preview.hidden = previewHtml === "";
    preview.innerHTML = previewHtml;
    initializePrivateAudioPlayers(preview);
  }

  if (hint instanceof HTMLElement) {
    hint.textContent = buildPrivateConversationAudioHintText(artistId);
  }

  if (recordButton instanceof HTMLButtonElement) {
    const recordingSupported = isPrivateVoiceRecordingSupported();
    recordButton.disabled = !recordingSupported;
    recordButton.classList.toggle("is-recording", isRecording);
    recordButton.textContent = isRecording ? "Parar gravacao" : "Gravar voz";
    recordButton.title = recordingSupported
      ? isRecording
        ? "Parar gravação"
        : "Gravar mensagem de voz"
      : "O teu browser não suporta gravação de voz";
  }

  if (attachButton instanceof HTMLButtonElement) {
    attachButton.disabled = isRecording;
  }

  if (fileInput instanceof HTMLInputElement) {
    fileInput.disabled = isRecording;
  }

  if (textInput instanceof HTMLTextAreaElement) {
    textInput.required = false;
    textInput.disabled = isVoiceMessageMode;
    textInput.placeholder = isVoiceMessageMode
      ? "A mensagem de voz será enviada sem texto."
      : "Escreve uma mensagem privada...";
  }
}

function readAudioDurationFromObjectUrl(objectUrl) {
  return new Promise((resolve) => {
    if (!objectUrl) {
      resolve(null);
      return;
    }

    const audio = document.createElement("audio");
    let finished = false;

    const finish = (duration) => {
      if (finished) {
        return;
      }

      finished = true;
      audio.removeAttribute("src");
      audio.load();
      resolve(Number.isFinite(duration) ? Number(duration) : null);
    };

    const timeoutId = window.setTimeout(() => finish(null), 4000);

    audio.preload = "metadata";
    audio.onloadedmetadata = () => {
      clearTimeout(timeoutId);
      finish(audio.duration);
    };
    audio.onerror = () => {
      clearTimeout(timeoutId);
      finish(null);
    };
    audio.src = objectUrl;
  });
}

async function attachPrivateConversationAudioFile(file) {
  if (!(file instanceof File)) {
    return;
  }

  if (!Number.isInteger(currentPrivateConversationArtistId) || currentPrivateConversationArtistId <= 0) {
    showTempMessage("Conversa inválida.", "warning");
    return;
  }

  if (!file.type || !file.type.toLowerCase().startsWith("audio/")) {
    showTempMessage("Escolhe um ficheiro de áudio válido.", "warning");
    return;
  }

  if (file.size > PRIVATE_AUDIO_MAX_BYTES) {
    showTempMessage("O ficheiro de áudio deve ter no máximo 10 MB.", "warning");
    return;
  }

  const objectUrl = URL.createObjectURL(file);
  const durationSeconds = await readAudioDurationFromObjectUrl(objectUrl);

  setPrivateConversationPendingAudio(currentPrivateConversationArtistId, {
    file,
    objectUrl,
    durationSeconds,
    source: "audio_file",
    displayName: file.name || "Áudio anexado",
    sizeBytes: file.size,
  });

  refreshPrivateConversationComposerUi();
}

function getPrivateVoiceRecordingExtension(mimeType) {
  const normalized = (mimeType || "").toLowerCase();
  if (normalized.includes("ogg")) {
    return "ogg";
  }

  if (normalized.includes("mp4")) {
    return "m4a";
  }

  return "webm";
}

function resolvePrivateConversationRecordingPromise() {
  if (typeof privateConversationAudioRecorderStopResolver === "function") {
    privateConversationAudioRecorderStopResolver();
  }

  privateConversationAudioRecorderStopResolver = null;
}

async function stopPrivateConversationVoiceRecording(
  { persist = true, limitReached = false } = {},
) {
  if (!(privateConversationAudioRecorder instanceof MediaRecorder)) {
    return;
  }

  if (privateConversationAudioRecorder.state === "inactive") {
    return;
  }

  privateConversationAudioRecorderPersistOnStop = persist;
  privateConversationAudioRecorderLimitReached = limitReached;

  await new Promise((resolve) => {
    privateConversationAudioRecorderStopResolver = resolve;
    privateConversationAudioRecorder.stop();
  });
}

async function startPrivateConversationVoiceRecording() {
  if (!isPrivateVoiceRecordingSupported()) {
    showTempMessage("O teu browser não suporta gravação de voz.", "warning");
    return;
  }

  if (!Number.isInteger(currentPrivateConversationArtistId) || currentPrivateConversationArtistId <= 0) {
    showTempMessage("Conversa inválida.", "warning");
    return;
  }

  if (
    privateConversationAudioRecorder instanceof MediaRecorder &&
    privateConversationAudioRecorder.state !== "inactive"
  ) {
    await stopPrivateConversationVoiceRecording({ persist: false });
  }

  clearPrivateConversationPendingAudio(currentPrivateConversationArtistId);

  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      audio: true,
    });
    const mimeType = getPrivateVoiceRecordingMimeType();
    const recorder = mimeType
      ? new MediaRecorder(stream, { mimeType })
      : new MediaRecorder(stream);
    const recordingArtistId = currentPrivateConversationArtistId;

    privateConversationAudioRecorder = recorder;
    privateConversationAudioRecorderStream = stream;
    privateConversationAudioRecorderChunks = [];
    privateConversationAudioRecorderArtistId = recordingArtistId;
    privateConversationAudioRecorderStartedAt = Date.now();
    privateConversationAudioRecorderMimeType = mimeType;
    privateConversationAudioRecorderPersistOnStop = true;
    privateConversationAudioRecorderLimitReached = false;
    setPrivateConversationDraft(recordingArtistId, "");

    recorder.addEventListener("dataavailable", (event) => {
      if (event.data && event.data.size > 0) {
        privateConversationAudioRecorderChunks.push(event.data);
      }
    });

    recorder.addEventListener("stop", async () => {
      const chunks = privateConversationAudioRecorderChunks.slice();
      const recordedMimeType =
        recorder.mimeType || privateConversationAudioRecorderMimeType || "audio/webm";
      const durationSeconds = getPrivateConversationRecordingElapsedSeconds();
      const persist = privateConversationAudioRecorderPersistOnStop;
      const limitReached = privateConversationAudioRecorderLimitReached;
      const artistId = privateConversationAudioRecorderArtistId;

      clearPrivateConversationAudioRecorderInterval();
      stopPrivateConversationAudioRecorderStream();
      privateConversationAudioRecorder = null;
      privateConversationAudioRecorderChunks = [];
      privateConversationAudioRecorderArtistId = 0;
      privateConversationAudioRecorderStartedAt = 0;
      privateConversationAudioRecorderMimeType = "";
      privateConversationAudioRecorderPersistOnStop = true;
      privateConversationAudioRecorderLimitReached = false;

      if (persist && chunks.length > 0 && Number.isInteger(artistId) && artistId > 0) {
        const blob = new Blob(chunks, { type: recordedMimeType || "audio/webm" });

        if (blob.size > PRIVATE_AUDIO_MAX_BYTES) {
          showTempMessage("A gravação excedeu o limite de 10 MB.", "warning");
        } else {
          const extension = getPrivateVoiceRecordingExtension(recordedMimeType);
          const file = new File(
            [blob],
            `mensagem-voz-${Date.now()}.${extension}`,
            { type: blob.type || recordedMimeType || "audio/webm" },
          );
          const objectUrl = URL.createObjectURL(file);

          setPrivateConversationPendingAudio(artistId, {
            file,
            objectUrl,
            durationSeconds,
            source: "voice_recording",
            displayName: "Mensagem de voz",
            sizeBytes: file.size,
          });
          setPrivateConversationDraft(artistId, "");

          if (limitReached) {
            showTempMessage("Limite de 1 minuto atingido.", "warning");
          }
        }
      }

      refreshPrivateConversationComposerUi();
      resolvePrivateConversationRecordingPromise();
    });

    recorder.addEventListener("error", () => {
      clearPrivateConversationAudioRecorderInterval();
      stopPrivateConversationAudioRecorderStream();
      privateConversationAudioRecorder = null;
      privateConversationAudioRecorderChunks = [];
      privateConversationAudioRecorderArtistId = 0;
      privateConversationAudioRecorderStartedAt = 0;
      privateConversationAudioRecorderMimeType = "";
      privateConversationAudioRecorderPersistOnStop = true;
      privateConversationAudioRecorderLimitReached = false;
      showTempMessage("Não foi possível gravar a mensagem de voz.", "error", 3000);
      refreshPrivateConversationComposerUi();
      resolvePrivateConversationRecordingPromise();
    });

    recorder.start(250);
    clearPrivateConversationAudioRecorderInterval();
    privateConversationAudioRecorderInterval = window.setInterval(() => {
      refreshPrivateConversationComposerUi();

      if (
        isRecordingPrivateConversationAudioForArtist(recordingArtistId) &&
        getPrivateConversationRecordingElapsedSeconds() >= PRIVATE_VOICE_MAX_DURATION_SECONDS
      ) {
        stopPrivateConversationVoiceRecording({
          persist: true,
          limitReached: true,
        });
      }
    }, 250);

    refreshPrivateConversationComposerUi();
  } catch (error) {
    showTempMessage(
      error?.message || "Não foi possível aceder ao microfone.",
      "error",
      3000,
    );
    stopPrivateConversationAudioRecorderStream();
    refreshPrivateConversationComposerUi();
  }
}

function buildEmojiPickerItemsHtml() {
  return CHAT_PICKER_EMOJIS.map(
    (emoji) =>
      `<button type="button" class="chat-emoji-item" data-chat-emoji="${emoji}" aria-label="Inserir emoji ${emoji}">${emoji}</button>`,
  ).join("");
}

if (globalChatEmojiPicker) {
  globalChatEmojiPicker.innerHTML = buildEmojiPickerItemsHtml();
}

function closeEmojiPickerByButton(button) {
  if (!(button instanceof HTMLElement)) {
    return;
  }

  const picker = button
    .closest(".chat-emoji-wrap")
    ?.querySelector(".chat-emoji-picker");
  if (!(picker instanceof HTMLElement)) {
    return;
  }

  button.setAttribute("aria-expanded", "false");
  picker.hidden = true;
}

function toggleEmojiPickerByButton(button) {
  if (!(button instanceof HTMLElement)) {
    return;
  }

  const wrap = button.closest(".chat-emoji-wrap");
  const picker = wrap?.querySelector(".chat-emoji-picker");
  if (!(picker instanceof HTMLElement)) {
    return;
  }

  document.querySelectorAll(".chat-emoji-picker").forEach((node) => {
    if (!(node instanceof HTMLElement)) {
      return;
    }

    if (node === picker) {
      return;
    }

    node.hidden = true;
    const ownerButton = node
      .closest(".chat-emoji-wrap")
      ?.querySelector("[data-chat-emoji-toggle]");
    if (ownerButton instanceof HTMLElement) {
      ownerButton.setAttribute("aria-expanded", "false");
    }
  });

  const shouldOpen = picker.hidden;
  picker.hidden = !shouldOpen;
  button.setAttribute("aria-expanded", shouldOpen ? "true" : "false");
}

function insertEmojiAtTextareaCursor(textarea, emoji) {
  if (!(textarea instanceof HTMLTextAreaElement)) {
    return;
  }

  const value = textarea.value || "";
  const start = Number.isInteger(textarea.selectionStart)
    ? textarea.selectionStart
    : value.length;
  const end = Number.isInteger(textarea.selectionEnd)
    ? textarea.selectionEnd
    : value.length;

  const nextValue = `${value.slice(0, start)}${emoji}${value.slice(end)}`;
  if (nextValue.length > 255) {
    return;
  }

  textarea.value = nextValue;
  const caret = start + emoji.length;
  textarea.focus();
  textarea.setSelectionRange(caret, caret);
  textarea.dispatchEvent(new Event("input", { bubbles: true }));
}

function formatGlobalChatDate(value) {
  const date = new Date(value || Date.now());
  if (!Number.isFinite(date.getTime())) {
    return "agora";
  }

  return date.toLocaleString("pt-PT", {
    day: "2-digit",
    month: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function setGlobalChatEmptyState(message = "Ainda não há mensagens.") {
  if (!globalChatMessages) {
    return;
  }

  globalChatMessages.innerHTML = `<div class="global-chat-empty">${escapeHtml(message)}</div>`;
}

function scrollGlobalChatToLatest() {
  if (!globalChatMessages) {
    return;
  }

  requestAnimationFrame(() => {
    if (!globalChatMessages) {
      return;
    }

    globalChatMessages.scrollTop = globalChatMessages.scrollHeight;
  });
}

function appendGlobalChatMessages(messages) {
  if (!globalChatMessages || !Array.isArray(messages)) {
    return;
  }

  if (messages.length === 0 && globalChatKnownMessageIds.size === 0) {
    setGlobalChatEmptyState();
    return;
  }

  const hasOnlyEmpty =
    globalChatMessages.children.length === 1 &&
    globalChatMessages.firstElementChild?.classList.contains(
      "global-chat-empty",
    );

  if (hasOnlyEmpty) {
    globalChatMessages.innerHTML = "";
  }

  let appendedCount = 0;

  messages.forEach((item) => {
    const messageId = Number(item?.id || 0);
    if (!Number.isInteger(messageId) || messageId <= 0) {
      return;
    }

    if (globalChatKnownMessageIds.has(messageId)) {
      return;
    }

    globalChatKnownMessageIds.add(messageId);
    if (messageId > globalChatLastMessageId) {
      globalChatLastMessageId = messageId;
    }

    const artistId = Number(item?.artist_id || 0);
    const safeAuthor = escapeHtml(item?.artist_name || "Artista");
    const safeText = escapeHtml(item?.message || "");
    const safeDate = escapeHtml(formatGlobalChatDate(item?.created_at));
    const isOwnMessage =
      currentSessionUserType === "artist" &&
      Number.isInteger(currentSessionAccountId) &&
      currentSessionAccountId > 0 &&
      artistId === currentSessionAccountId;

    const headerHtml = isOwnMessage
      ? `<div class="global-chat-item-header"><span class="global-chat-time">${safeDate}</span></div>`
      : `
      <div class="global-chat-item-header">
        <button type="button" class="global-chat-author-btn" data-chat-artist-id="${artistId}" title="Ver conta de ${safeAuthor}">
          <span class="global-chat-author">${safeAuthor}</span>
        </button>
        <span class="global-chat-time">${safeDate}</span>
      </div>
    `;

    const row = document.createElement("div");
    row.className = `global-chat-item${isOwnMessage ? " own" : ""}`;
    row.innerHTML = `
      ${headerHtml}
      <p class="global-chat-text">${safeText}</p>
    `;

    globalChatMessages.appendChild(row);
    appendedCount += 1;
  });

  if (appendedCount > 0) {
    scrollGlobalChatToLatest();
  }
}

async function openArtistProfileById(artistId) {
  if (!Number.isInteger(artistId) || artistId <= 0) {
    return;
  }

  const cached = currentArtistsById.get(artistId);
  if (cached) {
    showArtistDetails(cached);
    return;
  }

  try {
    const response = await fetch(
      `api/get_artist_profile.php?id=${encodeURIComponent(artistId)}`,
      {
        cache: "no-store",
      },
    );

    const data = await response.json();
    if (!response.ok || !data?.success || !data?.artist) {
      throw new Error(data?.message || "Não foi possível abrir o perfil.");
    }

    currentArtistsById.set(artistId, data.artist);
    showArtistDetails(data.artist);
  } catch (error) {
    showTempMessage(
      error.message || "Erro ao abrir o perfil do artista.",
      "error",
      3000,
    );
  }
}

async function fetchGlobalChatMessages({
  incremental = false,
  silent = false,
} = {}) {
  if (currentSessionUserType === "artist" && currentSessionArtistIsPending) {
    return;
  }

  if (!globalChatMessages || globalChatLoading) {
    return;
  }

  globalChatLoading = true;

  try {
    const afterId = incremental ? globalChatLastMessageId : 0;
    const endpoint = incremental
      ? `api/get_global_chat.php?after_id=${encodeURIComponent(afterId)}&limit=80`
      : "api/get_global_chat.php?limit=80";

    const response = await fetch(endpoint, { cache: "no-store" });
    const data = await response.json();

    if (!response.ok || !data?.success) {
      throw new Error(data?.message || "Erro ao carregar o chat geral.");
    }

    if (!incremental) {
      globalChatMessages.innerHTML = "";
      globalChatLastMessageId = 0;
      globalChatKnownMessageIds = new Set();
    }

    appendGlobalChatMessages(
      Array.isArray(data?.messages) ? data.messages : [],
    );

    const lastId = Number(data?.last_id || 0);
    if (Number.isInteger(lastId) && lastId > globalChatLastMessageId) {
      globalChatLastMessageId = lastId;
    }
  } catch (error) {
    if (!silent) {
      setGlobalChatEmptyState("Não foi possível carregar o chat.");
      showTempMessage(
        error.message || "Erro ao carregar o chat geral.",
        "error",
        3000,
      );
    }
  } finally {
    globalChatLoading = false;
  }
}

async function submitGlobalChatMessage(message) {
  const response = await fetch("api/send_global_chat.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ message }),
  });

  const data = await response.json();
  if (!response.ok || !data?.success) {
    throw new Error(data?.message || "Não foi possível enviar a mensagem.");
  }

  return data;
}

async function openGlobalChatModal() {
  if (!globalChatModal) {
    return;
  }

  if (currentSessionUserType !== "artist") {
    closeGlobalChatModal();
    return;
  }

  globalChatModal.hidden = false;
  updateGlobalChatPositionNearLegend();
  applyGlobalChatMinimizeState();
  syncGlobalChatComposerState();

  if (currentSessionArtistIsPending) {
    clearGlobalChatPolling();
    return;
  }

  if (!globalChatInitialized) {
    await fetchGlobalChatMessages({ incremental: false });
    globalChatInitialized = true;
  }

  startGlobalChatPolling();
  updateGlobalChatCounter();
  autoResizeGlobalChatInput();
}

function closeGlobalChatModal() {
  if (!globalChatModal) {
    return;
  }

  globalChatModal.hidden = true;
  clearGlobalChatPolling();
}

function syncGlobalChatAvailability() {
  const canUseChat = currentSessionUserType === "artist";

  if (canUseChat) {
    syncGlobalChatComposerState();
    openGlobalChatModal();
  } else {
    closeGlobalChatModal();
  }
}

function syncGlobalChatComposerState() {
  const isPendingArtist =
    currentSessionUserType === "artist" && currentSessionArtistIsPending;

  if (globalChatModal) {
    if (isPendingArtist) {
      globalChatModal.classList.add("is-minimized");
    } else {
      applyGlobalChatMinimizeState();
    }
  }

  if (globalChatMinimizeBtn) {
    globalChatMinimizeBtn.hidden = isPendingArtist;
  }

  if (globalChatMessages) {
    globalChatMessages.hidden = isPendingArtist;
  }

  if (globalChatForm) {
    globalChatForm.hidden = isPendingArtist;
  }

  if (globalChatPendingNotice) {
    globalChatPendingNotice.hidden = !isPendingArtist;
  }

  if (globalChatInput) {
    globalChatInput.disabled = isPendingArtist;

    if (isPendingArtist) {
      globalChatInput.blur();
    }
  }

  if (isPendingArtist) {
    clearGlobalChatPolling();
  }
}

function isPrivateInboxOpen() {
  return !!privateInboxDropdown?.classList.contains("show");
}

function setPrivateInboxHeader(title) {
  if (!privateInboxHeader) {
    return;
  }

  privateInboxHeader.textContent = title;
}

function resetPrivateInboxView() {
  if (
    privateConversationAudioRecorder instanceof MediaRecorder &&
    privateConversationAudioRecorder.state !== "inactive"
  ) {
    stopPrivateConversationVoiceRecording({ persist: false });
  }

  privateInboxViewMode = "conversations";
  currentPrivateConversationArtistId = 0;
  currentPrivateConversationArtistName = "";
  currentPrivateConversationArtistAvatar = "";
  currentPrivateConversationLastMessageId = 0;
  privateInboxList?.classList.remove("is-conversation-view");
  setPrivateInboxHeader("Mensagens privadas");
}

function getCurrentPrivateConversationAvatarUrl() {
  const rawAvatar = (currentPrivateConversationArtistAvatar || "")
    .toString()
    .trim();

  if (rawAvatar) {
    if (/^https?:\/\//i.test(rawAvatar)) {
      return rawAvatar;
    }

    if (rawAvatar.startsWith("/")) {
      return rawAvatar;
    }

    return `/beatmap/${rawAvatar.replace(/^\/+/, "")}`;
  }

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(
    currentPrivateConversationArtistName || "Artista",
  )}&background=7331df&color=fff&size=96`;
}

function buildPrivateConversationHeaderHtml() {
  const safeConversationName = escapeHtml(
    currentPrivateConversationArtistName || "Conversa",
  );
  const safeConversationAvatar = escapeHtml(
    getCurrentPrivateConversationAvatarUrl(),
  );

  return `
    <div class="private-inbox-view-header">
      <button type="button" class="private-inbox-back-btn" data-private-inbox-back>← Voltar</button>
      <div class="private-inbox-conversation-identity">
        <img
          src="${safeConversationAvatar}"
          alt="Foto de ${safeConversationName}"
          class="private-inbox-conversation-identity-avatar"
        >
        <button
          type="button"
          class="private-inbox-conversation-title-btn"
          data-private-conversation-profile="${currentPrivateConversationArtistId}"
          aria-label="Abrir perfil de ${safeConversationName}"
        >
          <strong class="private-inbox-conversation-title">${safeConversationName}</strong>
        </button>
      </div>
    </div>
  `;
}

function buildPrivateConversationComposerHtml() {
  const emojiItems = buildEmojiPickerItemsHtml();
  const artistId = currentPrivateConversationArtistId;
  const recordingSupported = isPrivateVoiceRecordingSupported();
  const isRecording = isRecordingPrivateConversationAudioForArtist(artistId);
  const audioHint = escapeHtml(buildPrivateConversationAudioHintText(artistId));
  const audioPreviewHtml = buildPrivateConversationAudioPreviewHtml(artistId);

  return `
    <form class="private-conversation-form" data-private-conversation-form>
      <div class="private-conversation-toolbar">
        <button type="button" class="private-conversation-tool-btn" data-private-audio-trigger>
          Anexar audio
        </button>
        <button
          type="button"
          class="private-conversation-tool-btn ${isRecording ? "is-recording" : ""}"
          data-private-audio-record
          ${recordingSupported ? "" : "disabled"}
        >
          ${isRecording ? "Parar gravacao" : "Gravar voz"}
        </button>
        <span class="private-conversation-audio-hint" data-private-audio-hint>${audioHint}</span>
        <input
          type="file"
          class="private-conversation-audio-input"
          data-private-audio-input
          accept="audio/*"
          hidden
        >
      </div>
      <div
        class="private-conversation-audio-preview"
        data-private-audio-preview
        ${audioPreviewHtml ? "" : "hidden"}
      >${audioPreviewHtml}</div>
      <div class="private-conversation-input-wrap">
        <textarea
          class="private-conversation-input"
          data-private-conversation-input
          rows="1"
          maxlength="255"
          placeholder="Escreve uma mensagem privada..."
        ></textarea>
        <div class="private-conversation-footer">
          <div class="private-conversation-actions">
            <div class="chat-emoji-wrap">
              <button
                type="button"
                class="chat-emoji-toggle"
                data-chat-emoji-toggle
                aria-label="Abrir emojis"
                aria-expanded="false"
              >
                🙂
              </button>
              <div class="chat-emoji-picker" hidden>
                ${emojiItems}
              </div>
            </div>
            <button type="submit" class="artist-upvote-btn artist-upvote-btn-modal">Enviar</button>
          </div>
        </div>
      </div>
    </form>
  `;
}

function setPrivateInboxUnreadBadge(unreadCount) {
  privateInboxUnreadCount = Math.max(0, Number(unreadCount) || 0);

  if (!privateInboxBadge) {
    return;
  }

  if (privateInboxUnreadCount <= 0) {
    privateInboxBadge.hidden = true;
    privateInboxBadge.textContent = "0";
    return;
  }

  privateInboxBadge.hidden = false;
  privateInboxBadge.textContent =
    privateInboxUnreadCount > 99 ? "99+" : String(privateInboxUnreadCount);
}

function clearPrivateInboxPolling() {
  if (privateInboxPollingTimer) {
    clearInterval(privateInboxPollingTimer);
    privateInboxPollingTimer = null;
  }
}

function startPrivateInboxPolling() {
  clearPrivateInboxPolling();
  privateInboxPollingTimer = setInterval(async () => {
    if (!isPrivateInboxOpen()) {
      clearPrivateInboxPolling();
      return;
    }

    if (
      privateInboxViewMode === "conversation" &&
      Number.isInteger(currentPrivateConversationArtistId) &&
      currentPrivateConversationArtistId > 0
    ) {
      await fetchPrivateConversationMessages(
        currentPrivateConversationArtistId,
        {
          silent: true,
          markRead: true,
        },
      );
      return;
    }

    await fetchPrivateInboxConversations({ silent: true });
  }, PRIVATE_INBOX_POLL_INTERVAL_MS);
}

function clearPrivateInboxBadgePolling() {
  if (privateInboxBadgePollingTimer) {
    clearInterval(privateInboxBadgePollingTimer);
    privateInboxBadgePollingTimer = null;
  }
}

function startPrivateInboxBadgePolling() {
  clearPrivateInboxBadgePolling();

  if (currentSessionUserType !== "artist") {
    setPrivateInboxUnreadBadge(0);
    return;
  }

  privateInboxBadgePollingTimer = setInterval(async () => {
    if (isPrivateInboxOpen()) {
      return;
    }

    await fetchPrivateInboxUnreadCount({ silent: true });
  }, PRIVATE_INBOX_BADGE_POLL_INTERVAL_MS);
}

function closePrivateInboxDropdown() {
  if (!privateInboxDropdown || !privateInboxBtn) {
    return;
  }

  privateInboxDropdown.classList.remove("show");
  privateInboxBtn.setAttribute("aria-expanded", "false");
  clearPrivateInboxPolling();
  resetPrivateInboxView();
}

function renderPrivateInboxConversations(conversations) {
  if (!privateInboxList) {
    return;
  }

  privateInboxList.classList.remove("is-conversation-view");

  if (!Array.isArray(conversations) || conversations.length === 0) {
    privateInboxList.innerHTML =
      '<div class="private-inbox-empty">Ainda não tens conversas privadas.</div>';
    return;
  }

  privateInboxList.innerHTML = conversations
    .map((item) => {
      const otherArtistId = Number(item?.other_artist_id || 0);
      const unreadCount = Number(item?.unread_count || 0);
      const artistNameRaw = (item?.other_artist_name || "Artista").toString();
      const safeName = escapeHtml(artistNameRaw);
      const previewText = (
        item?.last_message_preview ||
        item?.last_message ||
        (item?.last_audio_path
          ? item?.last_audio_source === "voice_recording"
            ? "Mensagem de voz"
            : "Ficheiro de áudio"
          : "")
      ).toString();
      const safeMessage = escapeHtml(previewText);
      const safeTime = escapeHtml(formatGlobalChatDate(item?.last_created_at));
      const avatarRaw = (item?.other_artist_image || "").toString().trim();
      const avatarNormalized = avatarRaw
        ? /^https?:\/\//i.test(avatarRaw)
          ? avatarRaw
          : avatarRaw.startsWith("/")
            ? avatarRaw
            : `/beatmap/${avatarRaw.replace(/^\/+/, "")}`
        : "";
      const avatarUrl =
        avatarNormalized ||
        `https://ui-avatars.com/api/?name=${encodeURIComponent(artistNameRaw)}&background=7331df&color=fff&size=96`;
      const safeAvatarUrl = escapeHtml(avatarUrl);
      const unreadBadge =
        unreadCount > 0
          ? `<span class="private-inbox-conversation-unread">${unreadCount > 99 ? "99+" : unreadCount}</span>`
          : "";

      return `
        <button
          type="button"
          class="private-inbox-conversation"
          data-other-artist-id="${otherArtistId}"
          title="Abrir conversa com ${safeName}"
        >
          <img src="${safeAvatarUrl}" alt="Foto de ${safeName}" class="private-inbox-conversation-avatar">
          <div class="private-inbox-conversation-content">
            <div class="private-inbox-conversation-top">
              <strong>${safeName}</strong>
              <span>${safeTime}</span>
            </div>
            <div class="private-inbox-conversation-preview-row">
              <div class="private-inbox-conversation-preview">${safeMessage}</div>
              ${unreadBadge}
            </div>
          </div>
        </button>
      `;
    })
    .join("");
}

function renderPrivateConversationMessages(messages) {
  if (!privateInboxList) {
    return;
  }

  const previousComposerInput = privateInboxList.querySelector(
    "[data-private-conversation-input]",
  );
  const shouldRestoreFocus =
    previousComposerInput instanceof HTMLTextAreaElement &&
    document.activeElement === previousComposerInput;
  const previousSelectionStart =
    previousComposerInput instanceof HTMLTextAreaElement
      ? previousComposerInput.selectionStart
      : null;
  const previousSelectionEnd =
    previousComposerInput instanceof HTMLTextAreaElement
      ? previousComposerInput.selectionEnd
      : null;

  if (previousComposerInput instanceof HTMLTextAreaElement) {
    setPrivateConversationDraft(
      currentPrivateConversationArtistId,
      previousComposerInput.value,
    );
  }

  const currentDraft = getPrivateConversationDraft(
    currentPrivateConversationArtistId,
  );

  privateInboxList.classList.add("is-conversation-view");

  if (!Array.isArray(messages) || messages.length === 0) {
    privateInboxList.innerHTML = `
      ${buildPrivateConversationHeaderHtml()}
      <div class="private-conversation-list">
        <div class="private-inbox-empty">Ainda não há mensagens nesta conversa.</div>
      </div>
      ${buildPrivateConversationComposerHtml()}
    `;

    const composerInput = privateInboxList.querySelector(
      "[data-private-conversation-input]",
    );
    if (composerInput instanceof HTMLTextAreaElement) {
      composerInput.value = currentDraft;
      if (
        shouldRestoreFocus &&
        Number.isInteger(previousSelectionStart) &&
        Number.isInteger(previousSelectionEnd)
      ) {
        composerInput.focus();
        composerInput.setSelectionRange(
          previousSelectionStart,
          previousSelectionEnd,
        );
      }
    }
    autoResizePrivateConversationInput(composerInput);
    refreshPrivateConversationComposerUi();
    return;
  }

  const listHtml = messages
    .map((item) => {
      const senderId = Number(item?.sender_artist_id || 0);
      const isOwn = senderId === currentSessionAccountId;
      const safeMessage = escapeHtml(item?.message || "");
      const safeTime = escapeHtml(formatGlobalChatDate(item?.created_at));
      const audioUrl = (item?.audio_path || "").toString().trim();
      const safeAudioUrl = escapeHtml(audioUrl);
      const hasAudio = audioUrl !== "";
      const isAudioOnly = hasAudio && safeMessage === "";
      const audioDurationRaw = item?.audio_duration_seconds;
      const audioLabel = hasAudio
        ? escapeHtml(
            item?.audio_source === "voice_recording"
              ? "Mensagem de voz"
              : item?.audio_original_name || "Áudio anexado",
          )
        : "";
      const audioMetaParts = [];
      if (
        audioDurationRaw !== null &&
        audioDurationRaw !== undefined &&
        audioDurationRaw !== "" &&
        Number.isFinite(Number(audioDurationRaw))
      ) {
        audioMetaParts.push(
          formatDurationSeconds(Number(audioDurationRaw)),
        );
      }
      if (Number(item?.audio_size_bytes || 0) > 0) {
        audioMetaParts.push(formatBytes(Number(item?.audio_size_bytes || 0)));
      }
      const safeAudioMeta = escapeHtml(audioMetaParts.join(" • "));
      const messageHtml = safeMessage
        ? `<div class="private-conversation-text">${safeMessage}</div>`
        : "";
      const audioHtml = hasAudio
        ? `
          <div class="private-conversation-audio-block">
            ${buildPrivateAudioPlayerHtml({
              audioUrl: safeAudioUrl,
              durationSeconds: audioDurationRaw,
              compact: isAudioOnly,
              downloadName:
                item?.audio_source === "voice_recording"
                  ? "mensagem-de-voz"
                  : item?.audio_original_name || "audio",
            })}
            <div class="private-conversation-audio-caption">
              <span>${audioLabel}</span>
              ${safeAudioMeta ? `<span>${safeAudioMeta}</span>` : ""}
            </div>
          </div>
        `
        : "";

        initializePrivateAudioPlayers(privateInboxList);
      return `
        <div class="private-conversation-item ${isOwn ? "is-own" : ""} ${isAudioOnly ? "is-audio-only" : ""}">
          <div class="private-conversation-bubble">${messageHtml}${audioHtml}</div>
          <div class="private-conversation-time">${safeTime}</div>
        </div>
      `;
    })
    .join("");

  privateInboxList.innerHTML = `
    ${buildPrivateConversationHeaderHtml()}
    <div class="private-conversation-list">${listHtml}</div>
    ${buildPrivateConversationComposerHtml()}
  `;

  const conversationList = privateInboxList.querySelector(
    ".private-conversation-list",
  );
  if (conversationList) {
    conversationList.scrollTop = conversationList.scrollHeight;
  }

  const composerInput = privateInboxList.querySelector(
    "[data-private-conversation-input]",
  );
  if (composerInput instanceof HTMLTextAreaElement) {
    composerInput.value = currentDraft;
    if (
      shouldRestoreFocus &&
      Number.isInteger(previousSelectionStart) &&
      Number.isInteger(previousSelectionEnd)
    ) {
      composerInput.focus();
      composerInput.setSelectionRange(previousSelectionStart, previousSelectionEnd);
    }
  }
  autoResizePrivateConversationInput(composerInput);
  refreshPrivateConversationComposerUi();
  initializePrivateAudioPlayers(privateInboxList);
}

async function fetchPrivateInboxConversations({ silent = false } = {}) {
  if (!privateInboxList || privateInboxLoading) {
    return;
  }

  privateInboxLoading = true;

  try {
    const response = await fetch(
      "api/get_private_messages.php?group_by_artist=1&limit=40",
      {
        cache: "no-store",
      },
    );
    const data = await response.json();

    if (!response.ok || !data?.success) {
      throw new Error(
        data?.message || "Não foi possível carregar as conversas privadas.",
      );
    }

    setPrivateInboxUnreadBadge(Number(data?.unread_count || 0));
    renderPrivateInboxConversations(
      Array.isArray(data?.conversations) ? data.conversations : [],
    );
  } catch (error) {
    if (!silent && privateInboxList) {
      privateInboxList.innerHTML =
        '<div class="private-inbox-empty">Erro ao carregar conversas.</div>';
      showTempMessage(
        error.message || "Erro ao carregar conversas privadas.",
        "error",
        3000,
      );
    }
  } finally {
    privateInboxLoading = false;
  }
}

async function fetchPrivateConversationMessages(
  otherArtistId,
  { silent = false, markRead = true } = {},
) {
  if (!privateInboxList || privateInboxLoading) {
    return;
  }

  if (!Number.isInteger(otherArtistId) || otherArtistId <= 0) {
    return;
  }

  privateInboxLoading = true;

  try {
    const endpoint = markRead
      ? `api/get_private_messages.php?conversation_with=${encodeURIComponent(otherArtistId)}&limit=100&mark_read=1`
      : `api/get_private_messages.php?conversation_with=${encodeURIComponent(otherArtistId)}&limit=100`;

    const response = await fetch(endpoint, {
      cache: "no-store",
    });
    const data = await response.json();

    if (!response.ok || !data?.success) {
      throw new Error(data?.message || "Não foi possível carregar a conversa.");
    }

    setPrivateInboxUnreadBadge(Number(data?.unread_count || 0));
    const lastId = Math.max(0, Number(data?.last_id || 0));
    const shouldRenderConversation =
      currentPrivateConversationArtistId !== otherArtistId ||
      !privateInboxList.classList.contains("is-conversation-view") ||
      lastId !== currentPrivateConversationLastMessageId;

    if (shouldRenderConversation) {
      renderPrivateConversationMessages(
        Array.isArray(data?.messages) ? data.messages : [],
      );
      currentPrivateConversationLastMessageId = lastId;
    }
  } catch (error) {
    if (!silent && privateInboxList) {
      privateInboxList.innerHTML =
        '<div class="private-inbox-empty">Erro ao carregar conversa.</div>';
      showTempMessage(
        error.message || "Erro ao carregar conversa privada.",
        "error",
        3000,
      );
    }
  } finally {
    privateInboxLoading = false;
  }
}

async function openPrivateConversation(
  otherArtistId,
  otherArtistName = "",
  otherArtistAvatar = "",
) {
  if (!Number.isInteger(otherArtistId) || otherArtistId <= 0) {
    return;
  }

  privateInboxViewMode = "conversation";
  currentPrivateConversationLastMessageId = -1;
  currentPrivateConversationArtistId = otherArtistId;
  currentPrivateConversationArtistName = (
    otherArtistName || "Artista"
  ).toString();
  currentPrivateConversationArtistAvatar = (otherArtistAvatar || "")
    .toString()
    .trim();
  setPrivateInboxHeader("Conversa");

  if (privateInboxList) {
    privateInboxList.innerHTML =
      '<div class="private-inbox-empty">A carregar conversa...</div>';
  }

  await fetchPrivateConversationMessages(otherArtistId, {
    silent: false,
    markRead: true,
  });
}

async function fetchPrivateInboxUnreadCount({ silent = true } = {}) {
  if (privateInboxBadgeLoading) {
    return;
  }

  privateInboxBadgeLoading = true;

  try {
    const response = await fetch(
      "api/get_private_messages.php?include_messages=0",
      {
        cache: "no-store",
      },
    );
    const data = await response.json();

    if (!response.ok || !data?.success) {
      throw new Error(
        data?.message || "Não foi possível atualizar mensagens privadas.",
      );
    }

    setPrivateInboxUnreadBadge(Number(data?.unread_count || 0));
  } catch (error) {
    if (!silent) {
      showTempMessage(
        error.message || "Erro ao atualizar mensagens privadas.",
        "error",
        3000,
      );
    }
  } finally {
    privateInboxBadgeLoading = false;
  }
}

async function submitPrivateMessage(recipientArtistId, message, pendingAudio = null) {
  const formData = new FormData();
  formData.append("recipient_artist_id", String(recipientArtistId));

  if (message) {
    formData.append("message", message);
  }

  if (pendingAudio?.file instanceof File) {
    formData.append("audio", pendingAudio.file, pendingAudio.file.name || "audio");
    formData.append("audio_source", pendingAudio.source || "audio_file");
    if (Number.isFinite(pendingAudio.durationSeconds)) {
      formData.append(
        "audio_duration_seconds",
        String(Number(pendingAudio.durationSeconds).toFixed(2)),
      );
    }
  }

  const response = await fetch("api/send_private_message.php", {
    method: "POST",
    body: formData,
  });

  const data = await response.json();
  if (!response.ok || !data?.success) {
    throw new Error(
      data?.message || "Não foi possível enviar mensagem privada.",
    );
  }

  return data;
}

async function openPrivateMessagePromptForArtist(artist) {
  const recipientArtistId = getArtistNumericId(artist);
  if (!Number.isInteger(recipientArtistId) || recipientArtistId <= 0) {
    return;
  }

  if (currentSessionUserType !== "artist") {
    showTempMessage(
      "Apenas artistas podem enviar mensagens privadas.",
      "warning",
    );
    return;
  }

  if (currentSessionArtistIsPending) {
    showTempMessage(
      "A tua conta está pendente. Não podes enviar mensagens privadas.",
      "warning",
    );
    return;
  }

  if (recipientArtistId === currentSessionAccountId) {
    showTempMessage(
      "Não podes enviar mensagem para a tua própria conta.",
      "warning",
    );
    return;
  }

  if (!privateInboxDropdown || !privateInboxBtn) {
    return;
  }

  privateInboxDropdown.classList.add("show");
  privateInboxBtn.setAttribute("aria-expanded", "true");
  startPrivateInboxPolling();

  const recipientName = (artist?.name || "Artista").toString();
  const recipientAvatar = (artist?.image || artist?.profile_picture || "")
    .toString()
    .trim();

  await openPrivateConversation(
    recipientArtistId,
    recipientName,
    recipientAvatar,
  );
}

function syncPrivateInboxAvailability() {
  if (!privateInbox) {
    return;
  }

  const canUsePrivateMessages = currentSessionUserType === "artist";

  if (canUsePrivateMessages) {
    privateInbox.hidden = false;
    resetPrivateInboxView();
    startPrivateInboxBadgePolling();
    fetchPrivateInboxUnreadCount({ silent: true });
    return;
  }

  closePrivateInboxDropdown();
  clearPrivateInboxBadgePolling();
  setPrivateInboxUnreadBadge(0);
  privateInbox.hidden = true;
}

window.addEventListener("resize", () => {
  updateGlobalChatPositionNearLegend();
});

window.addEventListener("visibilitychange", () => {
  if (document.visibilityState !== "visible") {
    return;
  }

  if (currentSessionUserType !== "artist") {
    return;
  }

  if (isPrivateInboxOpen()) {
    if (
      privateInboxViewMode === "conversation" &&
      Number.isInteger(currentPrivateConversationArtistId) &&
      currentPrivateConversationArtistId > 0
    ) {
      fetchPrivateConversationMessages(currentPrivateConversationArtistId, {
        silent: true,
        markRead: true,
      });
      return;
    }

    fetchPrivateInboxConversations({ silent: true });
    return;
  }

  fetchPrivateInboxUnreadCount({ silent: true });
});

window.addEventListener("focus", () => {
  if (currentSessionUserType !== "artist") {
    return;
  }

  if (isPrivateInboxOpen()) {
    if (
      privateInboxViewMode === "conversation" &&
      Number.isInteger(currentPrivateConversationArtistId) &&
      currentPrivateConversationArtistId > 0
    ) {
      fetchPrivateConversationMessages(currentPrivateConversationArtistId, {
        silent: true,
        markRead: true,
      });
      return;
    }

    fetchPrivateInboxConversations({ silent: true });
    return;
  }

  fetchPrivateInboxUnreadCount({ silent: true });
});

function getNormalizedRect(element) {
  const rect = element.getBoundingClientRect();
  const top = Math.max(8, rect.top - ONBOARDING_HIGHLIGHT_PADDING);
  const left = Math.max(8, rect.left - ONBOARDING_HIGHLIGHT_PADDING);
  const right = Math.min(
    window.innerWidth - 8,
    rect.right + ONBOARDING_HIGHLIGHT_PADDING,
  );
  const bottom = Math.min(
    window.innerHeight - 8,
    rect.bottom + ONBOARDING_HIGHLIGHT_PADDING,
  );

  return {
    top,
    left,
    width: Math.max(40, right - left),
    height: Math.max(36, bottom - top),
  };
}

function positionOnboardingTooltip(highlightRect) {
  if (!onboardingTooltip) return;

  const tooltipRect = onboardingTooltip.getBoundingClientRect();
  const margin = 14;
  const defaultTop = highlightRect.top + highlightRect.height + margin;
  const canPlaceBottom =
    defaultTop + tooltipRect.height <= window.innerHeight - 10;

  const top = canPlaceBottom
    ? defaultTop
    : Math.max(10, highlightRect.top - tooltipRect.height - margin);

  const centeredLeft =
    highlightRect.left + highlightRect.width / 2 - tooltipRect.width / 2;
  const left = Math.min(
    window.innerWidth - tooltipRect.width - 10,
    Math.max(10, centeredLeft),
  );

  onboardingTooltip.style.top = `${top}px`;
  onboardingTooltip.style.left = `${left}px`;
}

function positionOnboardingTooltipCentered() {
  if (!onboardingTooltip) return;

  const tooltipRect = onboardingTooltip.getBoundingClientRect();
  const top = Math.max(10, (window.innerHeight - tooltipRect.height) / 2);
  const left = Math.max(10, (window.innerWidth - tooltipRect.width) / 2);

  onboardingTooltip.style.top = `${top}px`;
  onboardingTooltip.style.left = `${left}px`;
}

function isOnboardingElementVisible(target) {
  if (!target) {
    return false;
  }

  const style = window.getComputedStyle(target);
  if (
    style.display === "none" ||
    style.visibility === "hidden" ||
    Number(style.opacity || "1") <= 0
  ) {
    return false;
  }

  const rect = target.getBoundingClientRect();
  if (rect.width <= 6 || rect.height <= 6) {
    return false;
  }

  return (
    rect.bottom > 0 &&
    rect.right > 0 &&
    rect.top < window.innerHeight &&
    rect.left < window.innerWidth
  );
}

function isOnboardingStepUiReady(step, target) {
  if (!isOnboardingElementVisible(target)) {
    return false;
  }

  if (step?.id === "artist-sort" || step?.id === "artist-genre-filter") {
    const filtersVisible =
      !!artistsFilters &&
      window.getComputedStyle(artistsFilters).display !== "none";
    const sidebarOpen = !!sidebar?.classList?.contains("open");
    const infoVisible = !!infoBox && infoBox.style.display !== "none";

    return filtersVisible && sidebarOpen && infoVisible;
  }

  return true;
}

function getOnboardingSteps() {
  const isDistritosMode = getEffectiveLayerType() === "distritos";

  return [
    {
      id: "intro",
      mode: "intro",
      title: "Bem-vindo ao BeatMap",
      description:
        "Tour rápido pelos controlos atualizados do mapa para começares em segundos.",
    },
    {
      id: "menu",
      selector: "#menuToggle",
      title: "Menu lateral",
      description:
        "Usa este botão para abrir e fechar o painel lateral com pesquisa e resultados.",
    },
    {
      id: "search",
      selector: "#cityInput",
      title: "Pesquisa principal",
      description: isDistritosMode
        ? "No modo Distritos, pesquisa e salta diretamente para o distrito pretendido."
        : "No modo Concelhos, pesquisa cidades/concelhos e centra o mapa rapidamente.",
    },
    {
      id: "search-mode",
      selector: "#searchModeSwitcher",
      title: "Pesquisa por género",
      description:
        "Alterna entre pesquisa por localização e pesquisa musical por géneros de artistas.",
    },
    {
      id: "layer",
      selector: "#layerSwitcher",
      title: "Troca de camada",
      description: "Alterna entre visualização por Concelhos e por Distritos.",
    },
    {
      id: "artist-sort",
      selector: "#artistSortInput",
      title: "Ordenar artistas",
      description:
        "Depois de escolheres uma zona ou género, ordena os artistas por critério no painel.",
      beforeStep: async () => {
        await selectRandomCouncilForOnboarding("sort", {
          keepCurrentSelection: false,
        });
      },
    },
    {
      id: "artist-genre-filter",
      selector: "#artistGenreInput",
      title: "Filtro de géneros",
      description:
        "Refina a lista de artistas com o filtro de géneros para resultados mais rápidos.",
      beforeStep: async () => {
        await selectRandomCouncilForOnboarding("filter", {
          keepCurrentSelection: true,
        });
      },
    },
    {
      id: "random",
      selector: "#randomBtn",
      title: "Sugestão aleatória",
      description: isDistritosMode
        ? "Descobre um distrito aleatório com artistas para explorar música nova."
        : "Descobre uma zona aleatória com artistas para explorar música nova.",
    },
    {
      id: "locate",
      selector: "#locateBtn",
      title: "Minha localização",
      description:
        "Este botão tenta localizar-te no mapa (requer permissão do navegador).",
    },
    {
      id: "account",
      selector: "#accountBtn",
      title: "Conta",
      description:
        "No menu da conta podes editar o perfil e terminar sessão quando quiseres.",
    },
    {
      id: "finish",
      mode: "outro",
      title: "Tutorial concluído",
      description:
        "Pronto! Já conheces os principais controlos. Agora explora o mapa ao teu ritmo.",
    },
  ];
}

function closeOnboardingTutorial(markAsSeen = true) {
  if (!onboardingActive || !onboardingOverlay) {
    return;
  }

  if (onboardingVisibilityRetryTimer) {
    clearTimeout(onboardingVisibilityRetryTimer);
    onboardingVisibilityRetryTimer = null;
  }

  cleanupOnboardingCouncilSelection();
  onboardingActive = false;
  onboardingPreparedStepId = null;
  onboardingVisibilityRetryStepId = null;
  onboardingVisibilityRetryCount = 0;
  onboardingOverlay.hidden = true;
  document.body.classList.remove("onboarding-open");
  window.removeEventListener("resize", renderOnboardingStep);
}

function moveOnboardingStep(direction) {
  if (!onboardingActive) return;

  const currentStep = onboardingSteps[onboardingCurrentIndex];
  if (
    direction > 0 &&
    currentStep?.id === "artist-genre-filter" &&
    onboardingAutoCouncilSelected
  ) {
    cleanupOnboardingCouncilSelection();
  }

  const nextIndex = onboardingCurrentIndex + direction;

  if (nextIndex < 0) {
    return;
  }

  if (nextIndex >= onboardingSteps.length) {
    closeOnboardingTutorial(true);
    return;
  }

  onboardingPreparedStepId = null;
  onboardingVisibilityRetryStepId = null;
  onboardingVisibilityRetryCount = 0;
  if (onboardingVisibilityRetryTimer) {
    clearTimeout(onboardingVisibilityRetryTimer);
    onboardingVisibilityRetryTimer = null;
  }

  onboardingCurrentIndex = nextIndex;
  void renderOnboardingStep();
}

async function selectRandomCouncilForOnboarding(
  stepName,
  { keepCurrentSelection = false } = {},
) {
  const waitForArtistFiltersVisible = async (timeoutMs = 4500) => {
    const startedAt = Date.now();

    while (Date.now() - startedAt < timeoutMs) {
      if (!onboardingActive) {
        return false;
      }

      const filtersVisible =
        !!artistsFilters &&
        window.getComputedStyle(artistsFilters).display !== "none";

      if (filtersVisible) {
        return true;
      }

      await new Promise((resolve) => setTimeout(resolve, 120));
    }

    return false;
  };

  if (
    keepCurrentSelection &&
    onboardingAutoCouncilSelected &&
    municipioSelecionado
  ) {
    if (!sidebar.classList.contains("open")) {
      sidebar.classList.add("open");
    }

    const currentSelectionReady = await waitForArtistFiltersVisible(2200);
    if (currentSelectionReady) {
      return;
    }
  }

  if (onboardingCouncilSelectionInProgress) {
    return;
  }

  onboardingCouncilSelectionInProgress = true;

  try {
    if (getEffectiveLayerType() !== "municipios") {
      switchLayer("municipios");
    }

    await loadAndShowMunicipios();
    await ensureArtistCoverageLoaded();

    const layers = municipiosLayer?.getLayers?.() || [];
    const filteredLayers = layers.filter((layer) => {
      if (!layer?.feature) return false;
      if (layer._selected) return false;

      const municipio = normalizeText(getFeatureMunicipioName(layer.feature));
      return municipio && artistCouncilsSet?.has(municipio);
    });

    if (filteredLayers.length === 0) {
      return;
    }

    const shuffledLayers = [...filteredLayers].sort(() => Math.random() - 0.5);
    const maxAttempts = Math.min(5, shuffledLayers.length);

    for (let attempt = 0; attempt < maxAttempts; attempt += 1) {
      const candidateLayer = shuffledLayers[attempt];
      if (!candidateLayer?.feature) {
        continue;
      }

      selecionarMunicipio(candidateLayer.feature, candidateLayer);
      onboardingAutoCouncilSelected = true;

      const filtersReady = await waitForArtistFiltersVisible(4500);
      if (filtersReady) {
        const councilName =
          getFeatureMunicipioName(candidateLayer.feature) || "concelho";
        if (!keepCurrentSelection) {
          showTempMessage(
            `Exemplo do tutorial (${stepName}): ${councilName}`,
            "info",
            1600,
          );
        }
        return;
      }

      clearSelectedRegion();
      infoBox.style.display = "none";
      infoBoxVisible = false;
      artistDetailsPanel.style.display = "none";
    }

    onboardingAutoCouncilSelected = false;
  } catch (error) {
    console.warn("Falha ao selecionar concelho para o tutorial:", error);
  } finally {
    onboardingCouncilSelectionInProgress = false;
  }
}

function cleanupOnboardingCouncilSelection() {
  if (!onboardingAutoCouncilSelected) {
    return;
  }

  clearSelectedRegion();

  if (portugalLayer) {
    map.fitBounds(portugalLayer.getBounds(), { padding: [50, 50] });
  } else {
    map.setView([20, 0], 2);
  }

  infoBox.style.display = "none";
  infoBoxVisible = false;
  artistDetailsPanel.style.display = "none";
  sidebar.classList.remove("open");
  onboardingAutoCouncilSelected = false;
}

async function renderOnboardingStep() {
  if (!onboardingActive || !onboardingHighlight || !onboardingTooltip) {
    return;
  }

  const renderToken = ++onboardingRenderToken;

  const step = onboardingSteps[onboardingCurrentIndex];
  if (!step) {
    closeOnboardingTutorial(true);
    return;
  }

  const stepId = step.id || `step-${onboardingCurrentIndex}`;

  if (onboardingVisibilityRetryStepId !== stepId) {
    onboardingVisibilityRetryStepId = stepId;
    onboardingVisibilityRetryCount = 0;
    if (onboardingVisibilityRetryTimer) {
      clearTimeout(onboardingVisibilityRetryTimer);
      onboardingVisibilityRetryTimer = null;
    }
  }

  if (
    onboardingPreparedStepId !== stepId &&
    typeof step.beforeStep === "function"
  ) {
    await step.beforeStep();
    onboardingPreparedStepId = stepId;
  }

  if (!onboardingActive || renderToken !== onboardingRenderToken) {
    return;
  }

  let rect = null;
  if (step.selector) {
    const target = document.querySelector(step.selector);
    if (!target) {
      moveOnboardingStep(1);
      return;
    }

    const isReady = isOnboardingStepUiReady(step, target);
    if (!isReady) {
      if (onboardingVisibilityRetryCount >= 12) {
        moveOnboardingStep(1);
        return;
      }

      onboardingVisibilityRetryCount += 1;
      onboardingVisibilityRetryTimer = setTimeout(() => {
        onboardingVisibilityRetryTimer = null;
        if (
          onboardingActive &&
          onboardingCurrentIndex < onboardingSteps.length
        ) {
          void renderOnboardingStep();
        }
      }, 120);
      return;
    }

    onboardingVisibilityRetryCount = 0;

    rect = getNormalizedRect(target);
    onboardingHighlight.hidden = false;
    onboardingHighlight.style.top = `${rect.top}px`;
    onboardingHighlight.style.left = `${rect.left}px`;
    onboardingHighlight.style.width = `${rect.width}px`;
    onboardingHighlight.style.height = `${rect.height}px`;
  } else {
    onboardingHighlight.hidden = true;
  }

  onboardingStepCounter.textContent = `Passo ${onboardingCurrentIndex + 1} de ${onboardingSteps.length}`;
  onboardingTitle.textContent = step.title;
  onboardingDescription.textContent = step.description;
  onboardingPrevBtn.disabled = onboardingCurrentIndex === 0;
  onboardingNextBtn.textContent =
    step.mode === "intro"
      ? "Começar"
      : onboardingCurrentIndex === onboardingSteps.length - 1
        ? "Concluir"
        : "Próximo";

  if (rect) {
    positionOnboardingTooltip(rect);
  } else {
    positionOnboardingTooltipCentered();
  }
}

function startOnboardingTutorial() {
  if (!onboardingOverlay || !onboardingHighlight || !onboardingTooltip) {
    return;
  }

  onboardingSteps = getOnboardingSteps().filter(
    (step) => !step.selector || document.querySelector(step.selector),
  );

  if (onboardingSteps.length === 0) {
    return;
  }

  onboardingCurrentIndex = 0;
  onboardingActive = true;
  onboardingPreparedStepId = null;
  onboardingVisibilityRetryStepId = null;
  onboardingVisibilityRetryCount = 0;
  if (onboardingVisibilityRetryTimer) {
    clearTimeout(onboardingVisibilityRetryTimer);
    onboardingVisibilityRetryTimer = null;
  }
  ensureArtistCoverageLoaded().catch(() => {});
  onboardingOverlay.hidden = false;
  document.body.classList.add("onboarding-open");
  window.addEventListener("resize", renderOnboardingStep);
  void renderOnboardingStep();
}

async function maybeStartOnboardingTutorial() {
  try {
    const response = await fetch("api/onboarding_status.php", {
      cache: "no-store",
    });

    if (!response.ok) {
      return;
    }

    const data = await response.json();
    if (data?.success && data?.showTutorial === true) {
      startOnboardingTutorial();
    }
  } catch (error) {
    console.warn(
      "Não foi possível validar tutorial de primeira visita:",
      error,
    );
  }
}

function animateToLayerBounds(bounds, options = {}) {
  if (!bounds || !map) return;

  const padding = options.padding || [50, 50];
  const maxZoom = Number.isFinite(options.maxZoom)
    ? options.maxZoom
    : undefined;

  const currentCenter = map.getCenter();
  const targetCenter = bounds.getCenter();
  const distanceMeters = map.distance(currentCenter, targetCenter);
  const distanceKm = distanceMeters / 1000;

  const suggestedZoom = map.getBoundsZoom(bounds, { padding });
  const targetZoom =
    maxZoom !== undefined ? Math.min(suggestedZoom, maxZoom) : suggestedZoom;

  const dynamicDuration = Math.max(0.45, Math.min(1.4, distanceKm / 320));

  if (distanceKm >= 80) {
    map.flyTo(targetCenter, targetZoom, {
      duration: dynamicDuration,
      easeLinearity: 0.2,
      noMoveStart: false,
    });
    return;
  }

  map.fitBounds(bounds, {
    padding,
    maxZoom,
    animate: true,
    duration: Math.max(0.3, Math.min(0.5, dynamicDuration)),
  });
}

function normalizeText(value) {
  return (value || "")
    .toString()
    .trim()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/\s+/g, " ");
}

function getEffectiveLayerType() {
  const hasDistritosVisible = !!(
    distritosLayer && map.hasLayer(distritosLayer)
  );
  const hasMunicipiosVisible = !!(
    municipiosLayer && map.hasLayer(municipiosLayer)
  );

  if (hasDistritosVisible && !hasMunicipiosVisible) {
    return "distritos";
  }

  if (hasMunicipiosVisible && !hasDistritosVisible) {
    return "municipios";
  }

  return currentLayerType;
}

function getEffectiveSearchMode() {
  return searchModeToggle?.checked ? "genre" : "location";
}

function getMinSearchMessage() {
  return getEffectiveSearchMode() === "genre"
    ? "Seleciona um ou mais géneros para pesquisar artistas."
    : "Escreve pelo menos 2 letras para pesquisar.";
}

function updateSearchContextUI() {
  if (!cityInput) {
    return;
  }

  const isGenreMode = getEffectiveSearchMode() === "genre";
  const isDistritosMode = getEffectiveLayerType() === "distritos";
  cityInput.placeholder = isGenreMode
    ? "Pesquisar género musical..."
    : isDistritosMode
      ? "Pesquisar distrito..."
      : "Pesquisar cidade ou concelho...";

  if (searchModeSwitcher) {
    searchModeSwitcher.dataset.mode = isGenreMode ? "genre" : "location";
  }

  if (searchHelper && !cityInput.value.trim()) {
    searchHelper.textContent = getMinSearchMessage();
  }
}

async function loadGenreSearchIndex() {
  if (Array.isArray(genreSearchIndex)) {
    return genreSearchIndex;
  }

  if (!genreSearchIndexPromise) {
    genreSearchIndexPromise = (async () => {
      const response = await fetch("api/get_artists.php?summary=genres");

      if (!response.ok) {
        throw new Error(
          `Erro de Rede ${response.status}: ${response.statusText}`,
        );
      }

      const data = await response.json();

      if (data?.error) {
        throw new Error(data.error);
      }

      const genres = Array.isArray(data?.genres) ? data.genres : [];

      genreSearchIndex = genres
        .map((genre) => {
          if (typeof genre === "string") {
            return { name: genre, artistCount: 0 };
          }

          return {
            name: (genre?.name || "").toString().trim(),
            artistCount: Number(genre?.artist_count || 0),
          };
        })
        .filter((genre) => genre.name.length > 0);

      return genreSearchIndex;
    })().catch((error) => {
      genreSearchIndexPromise = null;
      throw error;
    });
  }

  return genreSearchIndexPromise;
}

function filterGenresByQuery(query) {
  const normalizedQuery = normalizeText(query);

  if (!Array.isArray(genreSearchIndex)) {
    return [];
  }

  return genreSearchIndex
    .filter((genre) => {
      if (!normalizedQuery) {
        return true;
      }
      return normalizeText(genre.name).includes(normalizedQuery);
    })
    .sort((a, b) => {
      if (b.artistCount !== a.artistCount) {
        return b.artistCount - a.artistCount;
      }

      return a.name.localeCompare(b.name, "pt", { sensitivity: "base" });
    })
    .map((genre) => ({
      name: genre.name,
      artistCount: genre.artistCount,
    }));
}

function getSelectedGenresList() {
  return Array.from(selectedGenres.values());
}

async function renderGenreChecklist(query = "") {
  showLoading(true);
  if (searchHelper) {
    searchHelper.textContent = "A carregar géneros...";
  }

  try {
    await loadGenreSearchIndex();
    const genreResults = filterGenresByQuery(query);

    resultsContainer.innerHTML = "";
    activeSearchResultIndex = -1;

    if (genreResults.length === 0) {
      resultsContainer.innerHTML =
        '<div class="result-item">Nenhum género encontrado</div>';
      resultsContainer.style.display = "block";
      if (searchHelper) {
        searchHelper.textContent =
          "Não encontrámos géneros. Tenta outro termo musical.";
      }
      return;
    }

    genreResults.forEach((genre) => {
      const isChecked = selectedGenres.has(genre.name);
      const item = document.createElement("label");
      item.className = "result-item genre-check-item";

      const countLabel =
        Number(genre.artistCount) > 0
          ? `${genre.artistCount} artista(s)`
          : "Sem contagem";

      item.innerHTML = `
        <input type="checkbox" class="genre-check-input" value="${escapeHtml(genre.name)}" ${isChecked ? "checked" : ""}>
        <div class="genre-check-content">
          <div class="result-name">${escapeHtml(genre.name)}</div>
          <div class="result-details">${countLabel}</div>
        </div>
      `;

      const checkbox = item.querySelector(".genre-check-input");
      checkbox.addEventListener("change", (event) => {
        const checked = event.target.checked;
        if (checked) {
          selectedGenres.add(genre.name);
        } else {
          selectedGenres.delete(genre.name);
        }

        runGenreSelectionSearch();
      });

      resultsContainer.appendChild(item);
    });

    resultsContainer.style.display = "block";

    const selectedCount = selectedGenres.size;
    if (searchHelper) {
      searchHelper.textContent =
        selectedCount > 0
          ? `${selectedCount} género(s) selecionado(s).`
          : "Seleciona um ou mais géneros para pesquisar artistas.";
    }
  } catch (error) {
    console.error("Erro ao carregar checklist de géneros:", error);
    activeSearchResultIndex = -1;
    resultsContainer.innerHTML =
      '<div class="result-item">Erro ao buscar géneros</div>';
    resultsContainer.style.display = "block";
    if (searchHelper) {
      searchHelper.textContent =
        "Não foi possível carregar os géneros agora. Tenta novamente.";
    }
  } finally {
    showLoading(false);
  }
}

function runGenreSelectionSearch() {
  const selected = getSelectedGenresList();

  if (selected.length === 0) {
    if (searchHelper) {
      searchHelper.textContent =
        "Seleciona um ou mais géneros para pesquisar artistas.";
    }

    councilArtistsSection.style.display = "block";
    artistsList.innerHTML =
      '<div style="padding:10px; color: var(--text-muted); text-align: center;">Seleciona géneros para mostrar artistas.</div>';
    return;
  }

  showGenresInfo(selected);
}

async function searchGenres(query) {
  await renderGenreChecklist(query);
}

function clearSelectedRegion() {
  if (municipioSelecionado && municipiosLayer) {
    municipioSelecionado._selected = false;
    municipiosLayer.resetStyle(municipioSelecionado);
    municipioSelecionado = null;
  }

  if (distritoSelecionado && distritosLayer) {
    distritoSelecionado._selected = false;
    distritosLayer.resetStyle(distritoSelecionado);
    distritoSelecionado = null;
  }
}

function escapeHtml(value) {
  return (value || "")
    .toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/\"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function getArtistNumericId(artist) {
  const id = Number(artist?.id || 0);
  return Number.isInteger(id) && id > 0 ? id : 0;
}

function findMunicipioLayerByName(councilName) {
  const normalizedCouncil = normalizeText(councilName);
  if (!normalizedCouncil || !municipiosLayer?.getLayers) {
    return null;
  }

  const layers = municipiosLayer.getLayers();
  for (const layer of layers) {
    if (!layer?.feature) {
      continue;
    }

    const layerCouncil = normalizeText(getFeatureMunicipioName(layer.feature));
    if (layerCouncil === normalizedCouncil) {
      return layer;
    }
  }

  return null;
}

async function fetchArtistProfileByIdForMap(artistId) {
  const response = await fetch(
    `api/get_artist_profile.php?id=${encodeURIComponent(artistId)}`,
    {
      cache: "no-store",
    },
  );

  let data = null;
  try {
    data = await response.json();
  } catch (error) {
    data = null;
  }

  if (!response.ok || !data?.success || !data?.artist) {
    throw new Error(data?.message || "Não foi possível localizar a tua conta no mapa.");
  }

  return data.artist;
}

async function waitForArtistCardElement(artistId, timeoutMs = 5000) {
  const timeout = Math.max(1000, Number(timeoutMs) || 5000);
  const startedAt = Date.now();

  while (Date.now() - startedAt <= timeout) {
    const card = document.querySelector(`[data-artist-card-id="${artistId}"]`);
    if (card instanceof HTMLElement) {
      return card;
    }

    await new Promise((resolve) => setTimeout(resolve, 140));
  }

  return null;
}

async function focusCurrentArtistOnMap() {
  if (currentSessionUserType !== "artist" || currentSessionAccountId <= 0) {
    showTempMessage("Só disponível para contas de artista.", "warning", 2600);
    return;
  }

  if (currentArtistSort !== "popularidade") {
    currentArtistSort = "popularidade";
    if (artistSortInput) {
      artistSortInput.value = currentArtistSort;
    }
    applyArtistFiltersAndRender();
  }

  try {
    await loadAndShowMunicipios();

    const ownArtist = await fetchArtistProfileByIdForMap(currentSessionAccountId);
    const ownCouncil = (ownArtist?.council || "").toString().trim();

    if (!ownCouncil) {
      throw new Error("A tua conta não tem concelho definido.");
    }

    selfMapFocusSortResetPending = true;
    selfMapFocusCouncilName = ownCouncil;

    if (getEffectiveLayerType() !== "municipios") {
      switchLayer("municipios");
    }

    const ownLayer = findMunicipioLayerByName(ownCouncil);
    if (!ownLayer?.feature) {
      throw new Error("Não foi possível encontrar o teu concelho no mapa.");
    }

    selecionarMunicipio(ownLayer.feature, ownLayer);

    const ownCard = await waitForArtistCardElement(currentSessionAccountId, 5000);
    if (!ownCard) {
      showTempMessage(
        "Concelho aberto. O teu cartão ainda não ficou disponível na lista.",
        "warning",
        3200,
      );
      return;
    }

    ownCard.scrollIntoView({ behavior: "smooth", block: "center" });
    ownCard.classList.add("artist-card-self-focus");
    setTimeout(() => {
      ownCard.classList.remove("artist-card-self-focus");
    }, 1800);
  } catch (error) {
    showTempMessage(
      error.message || "Não foi possível mostrar a tua conta no mapa.",
      "error",
      3400,
    );
  }
}

function isCurrentSessionOwnArtistId(artistId) {
  return (
    currentSessionUserType === "artist" &&
    Number.isInteger(currentSessionAccountId) &&
    currentSessionAccountId > 0 &&
    artistId === currentSessionAccountId
  );
}

function normalizeArtistUpvotes(artist) {
  const value = Number(artist?.upvotes || 0);
  if (!Number.isFinite(value) || value < 0) {
    return 0;
  }

  return Math.floor(value);
}

function hasArtistUpvoted(artist) {
  const raw = artist?.has_upvoted;
  return raw === true || raw === 1 || raw === "1";
}

function formatUpvotesText(upvotes) {
  return `${upvotes} upvote${upvotes === 1 ? "" : "s"}`;
}

function setUpvoteButtonsBusy(artistId, isBusy) {
  const buttons = document.querySelectorAll(
    `[data-upvote-button="${artistId}"]`,
  );

  buttons.forEach((button) => {
    button.disabled = isBusy;
    button.classList.toggle("is-loading", isBusy);
  });
}

function setArtistUpvoteUiState(artistId, upvotes, hasUpvoted) {
  const normalizedUpvotes = Math.max(0, Math.floor(Number(upvotes) || 0));
  const normalizedHasUpvoted = !!hasUpvoted;

  const artist = currentArtistsById.get(artistId);
  if (artist) {
    artist.upvotes = normalizedUpvotes;
    artist.has_upvoted = normalizedHasUpvoted;
    currentArtistsById.set(artistId, artist);
  }

  if (Array.isArray(currentArtistsRaw) && currentArtistsRaw.length > 0) {
    currentArtistsRaw = currentArtistsRaw.map((rawArtist) => {
      if (getArtistNumericId(rawArtist) !== artistId) {
        return rawArtist;
      }

      return {
        ...rawArtist,
        upvotes: normalizedUpvotes,
        has_upvoted: normalizedHasUpvoted,
      };
    });
  }

  const upvoteTags = document.querySelectorAll(
    `[data-artist-upvotes="${artistId}"]`,
  );
  upvoteTags.forEach((element) => {
    element.textContent = `▲ ${normalizedUpvotes}`;
  });

  const buttons = document.querySelectorAll(
    `[data-upvote-button="${artistId}"]`,
  );
  buttons.forEach((button) => {
    button.classList.toggle("is-active", normalizedHasUpvoted);
    button.setAttribute(
      "aria-pressed",
      normalizedHasUpvoted ? "true" : "false",
    );
  });

  const modalCounter = document.querySelector(
    `[data-artist-modal-upvotes="${artistId}"]`,
  );
  if (modalCounter) {
    modalCounter.textContent = formatUpvotesText(normalizedUpvotes);
  }

  if (currentArtistSort === "popularidade") {
    applyArtistFiltersAndRender();
  }
}

async function toggleArtistUpvote(artistId) {
  const response = await fetch("api/upvote_artist.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ artist_id: artistId }),
  });

  let data = null;
  try {
    data = await response.json();
  } catch (error) {
    data = null;
  }

  if (!response.ok || !data?.success) {
    throw new Error(data?.message || "Não foi possível registar o upvote.");
  }

  return data;
}

async function handleArtistUpvote(artistId) {
  if (!Number.isInteger(artistId) || artistId <= 0) {
    return;
  }

  if (isCurrentSessionOwnArtistId(artistId)) {
    showTempMessage("Não podes dar upvote à tua própria conta.", "warning", 2800);
    return;
  }

  setUpvoteButtonsBusy(artistId, true);

  try {
    const result = await toggleArtistUpvote(artistId);
    setArtistUpvoteUiState(artistId, result.upvotes, result.has_upvoted);
  } catch (error) {
    showTempMessage(
      error.message || "Erro ao votar no artista.",
      "error",
      3200,
    );
  } finally {
    setUpvoteButtonsBusy(artistId, false);
  }
}

function setReportButtonsBusy(artistId, isBusy) {
  const buttons = document.querySelectorAll(
    `[data-report-button="${artistId}"]`,
  );

  buttons.forEach((button) => {
    button.disabled = isBusy;
    button.classList.toggle("is-loading", isBusy);
  });
}

async function submitArtistReport(artistId, reason) {
  const payload = {
    artist_id: artistId,
  };

  if (typeof reason === "string" && reason.trim() !== "") {
    payload.reason = reason.trim();
  }

  const response = await fetch("api/report_artist.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(payload),
  });

  let data = null;
  try {
    data = await response.json();
  } catch (error) {
    data = null;
  }

  if (!response.ok || !data?.success) {
    throw new Error(data?.message || "Não foi possível denunciar o artista.");
  }

  return data;
}

async function handleArtistReport(artistId, artistName) {
  if (!Number.isInteger(artistId) || artistId <= 0) {
    return;
  }

  if (isCurrentSessionOwnArtistId(artistId)) {
    showTempMessage("Não podes denunciar a tua própria conta.", "warning", 2800);
    return;
  }

  const safeName = (artistName || "este artista").toString().trim();
  const shouldReport = window.confirm(
    `Tens a certeza que queres denunciar ${safeName}?`,
  );

  if (!shouldReport) {
    return;
  }

  const reasonInput = window.prompt("Motivo da denúncia (opcional):", "");

  if (reasonInput === null) {
    return;
  }

  setReportButtonsBusy(artistId, true);

  try {
    const result = await submitArtistReport(artistId, reasonInput);
    showTempMessage(
      result?.message || "Denúncia enviada com sucesso.",
      "success",
      3200,
    );
  } catch (error) {
    showTempMessage(
      error.message || "Erro ao denunciar artista.",
      "error",
      3400,
    );
  } finally {
    setReportButtonsBusy(artistId, false);
  }
}

const SOCIAL_PLATFORM_META = {
  instagram: {
    label: "Instagram",
    icon: "../beatmap/assets/social/instagram.png",
  },
  x: {
    label: "X",
    icon: "../beatmap/assets/social/x.png",
  },
  youtube: {
    label: "YouTube",
    icon: "../beatmap/assets/social/youtube.png",
  },
  youtube_music: {
    label: "YouTube Music",
    icon: "../beatmap/assets/social/youtubemusic-com.png",
  },
  tiktok: {
    label: "TikTok",
    icon: "../beatmap/assets/social/tiktok.png",
  },
  linkedin: {
    label: "LinkedIn",
    icon: "../beatmap/assets/social/linkedin.png",
  },
  tidal: {
    label: "Tidal",
    icon: "../beatmap/assets/social/tidal.png",
  },
  spotify: {
    label: "Spotify",
    icon: "../beatmap/assets/social/spotify.png",
  },
  soundcloud: {
    label: "SoundCloud",
    icon: "../beatmap/assets/social/soundcloud.png",
  },
  apple_music: {
    label: "Apple Music",
    icon: "../beatmap/assets/social/apple.png",
  },
  bandcamp: {
    label: "Bandcamp",
    icon: "../beatmap/assets/social/bandcamp.png",
  },
};

const MUSIC_APP_KEYS = new Set([
  "tidal",
  "spotify",
  "soundcloud",
  "apple_music",
  "youtube_music",
  "bandcamp",
]);

function normalizeSocialUrl(urlValue) {
  const raw = (urlValue || "").toString().trim();
  if (!raw) {
    return "";
  }

  const withProtocol = /^https?:\/\//i.test(raw) ? raw : `https://${raw}`;

  try {
    const parsed = new URL(withProtocol);
    if (!/^https?:$/i.test(parsed.protocol)) {
      return "";
    }
    return parsed.toString();
  } catch (error) {
    return "";
  }
}

function extractArtistSocialLinks(artist) {
  const rawSocialLinks = artist?.social_links;
  if (!rawSocialLinks) {
    return [];
  }

  let parsed = rawSocialLinks;

  if (typeof rawSocialLinks === "string") {
    try {
      parsed = JSON.parse(rawSocialLinks);
    } catch (error) {
      return [];
    }
  }

  if (!parsed || typeof parsed !== "object" || Array.isArray(parsed)) {
    return [];
  }

  return Object.entries(parsed)
    .map(([platformKey, platformUrl]) => {
      const normalizedUrl = normalizeSocialUrl(platformUrl);
      if (!normalizedUrl) {
        return null;
      }

      const key = (platformKey || "").toString().trim();
      const platformMeta = SOCIAL_PLATFORM_META[key] || null;
      const label = platformMeta?.label || key.replace(/_/g, " ");
      const icon = platformMeta?.icon || "";

      return {
        key,
        label,
        icon,
        url: normalizedUrl,
      };
    })
    .filter(Boolean);
}

function renderArtistSocialLinks(artist) {
  const links = extractArtistSocialLinks(artist);
  if (!links.length) {
    return {
      musicHtml: "",
      socialHtml: "",
    };
  }

  const renderLinksHtml = (items) =>
    items
      .map(
        (item) =>
          `<a class="artist-modal-social-link" href="${escapeHtml(item.url)}" target="_blank" rel="noopener noreferrer" aria-label="${escapeHtml(item.label)}" title="${escapeHtml(item.label)}">${item.icon ? `<img src="${escapeHtml(item.icon)}" alt="${escapeHtml(item.label)}">` : `<span>${escapeHtml(item.label)}</span>`}</a>`,
      )
      .join("");

  const musicApps = links.filter((item) => MUSIC_APP_KEYS.has(item.key));
  const socialNetworks = links.filter((item) => !MUSIC_APP_KEYS.has(item.key));

  return {
    musicHtml: musicApps.length ? renderLinksHtml(musicApps) : "",
    socialHtml: socialNetworks.length ? renderLinksHtml(socialNetworks) : "",
  };
}

function normalizePreviewUrl(rawUrl) {
  if (!rawUrl) {
    return "";
  }

  const trimmed = String(rawUrl).trim();
  if (!trimmed) {
    return "";
  }

  const withProtocol = /^https?:\/\//i.test(trimmed)
    ? trimmed
    : `https://${trimmed.replace(/^\/+/, "")}`;

  try {
    return new URL(withProtocol).toString();
  } catch (error) {
    return "";
  }
}

function detectMusicPreviewEmbed(rawUrl) {
  const normalizedUrl = normalizePreviewUrl(rawUrl);
  if (!normalizedUrl) {
    return null;
  }

  let url;
  try {
    url = new URL(normalizedUrl);
  } catch (error) {
    return null;
  }

  const host = url.hostname.toLowerCase().replace(/^www\./, "");
  const path = url.pathname || "";

  if (host.includes("spotify.com")) {
    const match = path.match(
      /\/(?:intl-[a-z]{2}\/)?(track|album|playlist|episode|show)\/([a-zA-Z0-9]+)/i,
    );
    if (match) {
      const type = String(match[1]).toLowerCase();
      const id = match[2];
      return {
        provider: "Spotify",
        embed_src: `https://open.spotify.com/embed/${type}/${encodeURIComponent(id)}?utm_source=generator`,
        embed_height: ["track", "episode"].includes(type) ? 80 : 120,
        url: normalizedUrl,
      };
    }
  }

  if (
    host.includes("youtube.com") ||
    host.includes("youtu.be") ||
    host.includes("music.youtube.com")
  ) {
    let videoId = "";

    if (host.includes("youtu.be")) {
      const match = path.match(/^\/([a-zA-Z0-9_-]{11})/);
      if (match) {
        videoId = match[1];
      }
    } else {
      const queryVideoId = url.searchParams.get("v") || "";
      if (/^[a-zA-Z0-9_-]{11}$/.test(queryVideoId)) {
        videoId = queryVideoId;
      } else {
        const match = path.match(/\/(?:shorts|embed)\/([a-zA-Z0-9_-]{11})/);
        if (match) {
          videoId = match[1];
        }
      }
    }

    if (videoId) {
      return {
        provider: host.includes("music.youtube.com")
          ? "YouTube Music"
          : "YouTube",
        embed_src: `https://www.youtube.com/embed/${encodeURIComponent(videoId)}?rel=0&modestbranding=1&playsinline=1`,
        embed_height: 190,
        url: normalizedUrl,
      };
    }
  }

  if (host.includes("soundcloud.com")) {
    return {
      provider: "SoundCloud",
      embed_src: `https://w.soundcloud.com/player/?url=${encodeURIComponent(normalizedUrl)}&color=%237331df&auto_play=false&hide_related=false&show_comments=false&show_user=true&show_reposts=false&visual=true`,
      embed_height: 120,
      url: normalizedUrl,
    };
  }

  if (host.includes("music.apple.com")) {
    const isAppleTrack = url.searchParams.has("i");
    return {
      provider: "Apple Music",
      embed_src: `https://embed.music.apple.com${path}${url.search}`,
      embed_height: isAppleTrack ? 140 : 180,
      url: normalizedUrl,
    };
  }

  return {
    provider: host || "Link",
    embed_src: "",
    embed_height: 0,
    url: normalizedUrl,
  };
}

function renderArtistMusicPreviews(artist) {
  const previewKeys = ["preview1", "preview2", "preview3"];
  const previewItems = previewKeys
    .map((key) => detectMusicPreviewEmbed(artist?.[key]))
    .filter(Boolean);

  if (!previewItems.length) {
    return "";
  }

  const embedsHtml = previewItems
    .map((item) => {
      if (!item.embed_src) {
        return `<a class="music-preview-link-fallback" href="${escapeHtml(item.url)}" target="_blank" rel="noopener noreferrer">Ouvir preview</a>`;
      }

      return `
        <iframe
          src="${escapeHtml(item.embed_src)}"
          width="100%"
          height="${Number(item.embed_height) || 180}"
          loading="lazy"
          referrerpolicy="strict-origin-when-cross-origin"
          allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
          allowfullscreen
          title="Preview ${escapeHtml(item.provider)}"
        ></iframe>
      `;
    })
    .join("");

  return `
    <div class="artist-modal-section">
      <h3>Previews de músicas</h3>
      <div class="music-preview-grid">${embedsHtml}</div>
    </div>
  `;
}

function closeArtistModal() {
  if (!artistModal) return;

  if (artistModal.hidden) {
    return;
  }

  artistModal.classList.remove("is-visible");
  artistModal.classList.add("is-closing");

  if (artistModalCloseTimeout) {
    clearTimeout(artistModalCloseTimeout);
  }

  artistModalCloseTimeout = setTimeout(() => {
    artistModal.hidden = true;
    artistModal.classList.remove("is-closing");
    artistModalCloseTimeout = null;
  }, ARTIST_MODAL_ANIMATION_MS);

  document.body.classList.remove("artist-modal-open");
}

function showArtistDetails(artist) {
  if (!artistModal || !artistModalBody) {
    return;
  }

  const artistId = getArtistNumericId(artist);
  const isOwnArtistAccount = isCurrentSessionOwnArtistId(artistId);
  const upvotes = normalizeArtistUpvotes(artist);
  const upvotedByUser = hasArtistUpvoted(artist);
  const safeName = escapeHtml(artist?.name || "Artista");
  const safeGenre = escapeHtml(artist?.genre || "Geral");
  const safeBio = escapeHtml(artist?.bio || "Sem biografia disponível.");
  const safeEmail = escapeHtml(artist?.email || "Não disponível");
  const safeDistrict = escapeHtml(artist?.district || "N/A");
  const safeCouncil = escapeHtml(artist?.council || "N/A");
  const socialLinksUi = renderArtistSocialLinks(artist);
  const musicPreviewsSection = renderArtistMusicPreviews(artist);
  const musicAppsCorner = socialLinksUi.musicHtml
    ? `<div class="artist-modal-music-corner"><div class="artist-modal-social-links">${socialLinksUi.musicHtml}</div></div>`
    : "";
  const socialNetworksInContacts = socialLinksUi.socialHtml
    ? `
      <div class="artist-modal-meta-social">
        <p><strong>Redes sociais:</strong></p>
        <div class="artist-modal-social-links">${socialLinksUi.socialHtml}</div>
      </div>
    `
    : "";
  const imageUrl =
    artist?.image ||
    `https://ui-avatars.com/api/?name=${encodeURIComponent(
      artist?.name || "Artista",
    )}&background=7331df&color=fff&size=256`;
  const modalUpvotesCounter =
    artistId > 0
      ? `<span class="artist-modal-upvotes" data-artist-modal-upvotes="${artistId}">${formatUpvotesText(upvotes)}</span>`
      : `<span class="artist-modal-upvotes">${formatUpvotesText(upvotes)}</span>`;
  const upvoteActions =
    artistId > 0 && !isOwnArtistAccount
      ? `
        <div class="artist-modal-actions">
          <button
            type="button"
            class="artist-upvote-btn artist-upvote-btn-modal ${upvotedByUser ? "is-active" : ""}"
            data-upvote-button="${artistId}"
            data-artist-id="${artistId}"
            aria-pressed="${upvotedByUser ? "true" : "false"}"
          >▲ Upvote</button>
          ${
            currentSessionUserType === "artist" &&
            artistId !== currentSessionAccountId
              ? `
            <button
              type="button"
              class="artist-private-message-btn"
              data-private-message-button="${artistId}"
              data-artist-id="${artistId}"
              ${
                currentSessionArtistIsPending
                  ? 'disabled title="Conta pendente: não podes enviar mensagens privadas"'
                  : `aria-label=\"Enviar mensagem privada para ${safeName}\"`
              }
            >✉ Mensagem privada</button>
          `
              : ""
          }
          <button
            type="button"
            class="artist-report-btn"
            data-report-button="${artistId}"
            data-artist-id="${artistId}"
            aria-label="Denunciar ${safeName}"
          >⚑ Denunciar</button>
        </div>
      `
      : "";

  artistModalBody.innerHTML = `
    <div class="artist-modal-header">
      <img src="${imageUrl}" alt="Foto de ${safeName}" class="artist-modal-avatar">
      <div class="artist-modal-title-wrap">
        <h2 id="artistModalTitle" class="artist-modal-title">${safeName}</h2>
        <span class="artist-modal-genre">${safeGenre}</span>
        ${modalUpvotesCounter}
        ${upvoteActions}
      </div>
      ${musicAppsCorner}
    </div>
    <div class="artist-modal-section">
      <h3>Biografia</h3>
      <p>${safeBio}</p>
    </div>
    ${musicPreviewsSection}
    <div class="artist-modal-meta">
      <div class="artist-modal-meta-layout">
        <div class="artist-modal-meta-left">
          <p><strong>Contacto:</strong> ${safeEmail}</p>
          <p><strong>Distrito:</strong> ${safeDistrict}</p>
          <p><strong>Concelho:</strong> ${safeCouncil}</p>
        </div>
        <div class="artist-modal-meta-right">
          ${socialNetworksInContacts}
        </div>
      </div>
    </div>
  `;

  if (artistModalCard) {
    artistModalCard.classList.toggle("is-own-artist", isOwnArtistAccount);
  }

  const modalUpvoteButton =
    artistId > 0
      ? artistModalBody.querySelector(`[data-upvote-button="${artistId}"]`)
      : null;

  if (modalUpvoteButton) {
    modalUpvoteButton.addEventListener("click", async (event) => {
      event.preventDefault();
      event.stopPropagation();
      await handleArtistUpvote(artistId);
    });
  }

  const modalReportButton =
    artistId > 0
      ? artistModalBody.querySelector(`[data-report-button="${artistId}"]`)
      : null;

  if (modalReportButton) {
    modalReportButton.addEventListener("click", async (event) => {
      event.preventDefault();
      event.stopPropagation();
      await handleArtistReport(artistId, artist?.name || "este artista");
    });
  }

  const modalPrivateMessageButton =
    artistId > 0
      ? artistModalBody.querySelector(
          `[data-private-message-button="${artistId}"]`,
        )
      : null;

  if (modalPrivateMessageButton) {
    modalPrivateMessageButton.addEventListener("click", async (event) => {
      event.preventDefault();
      event.stopPropagation();
      closeArtistModal();
      await openPrivateMessagePromptForArtist(artist);
    });
  }

  if (artistModalCloseTimeout) {
    clearTimeout(artistModalCloseTimeout);
    artistModalCloseTimeout = null;
  }

  artistModal.hidden = false;
  artistModal.classList.remove("is-closing");

  requestAnimationFrame(() => {
    artistModal.classList.add("is-visible");
  });

  document.body.classList.add("artist-modal-open");
}

artistModalClose?.addEventListener("click", closeArtistModal);
artistModalOverlay?.addEventListener("click", closeArtistModal);
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && onboardingActive) {
    closeOnboardingTutorial(true);
    return;
  }

  if (event.key === "Escape" && artistModal && !artistModal.hidden) {
    closeArtistModal();
  }
});

function getFeatureMunicipioName(feature) {
  return feature?.properties?.municipio || feature?.properties?.name || "";
}

function getFeatureDistritoName(feature) {
  return feature?.properties?.distrito || feature?.properties?.name || "";
}

function buildDistrictCoverageFromCouncils() {
  if (
    !(artistCouncilsSet instanceof Set) ||
    !(artistDistrictsSet instanceof Set)
  ) {
    return;
  }

  if (!municipiosData?.features?.length || artistCouncilsSet.size === 0) {
    return;
  }

  municipiosData.features.forEach((feature) => {
    const municipioNormalizado = normalizeText(
      getFeatureMunicipioName(feature),
    );
    if (!municipioNormalizado || !artistCouncilsSet.has(municipioNormalizado)) {
      return;
    }

    const distritoNormalizado = normalizeDistrictName(
      feature?.properties?.distrito_ilha || getFeatureDistritoName(feature),
    );

    if (distritoNormalizado) {
      artistDistrictsSet.add(distritoNormalizado);
    }
  });
}

async function ensureArtistCoverageLoaded() {
  if (artistCouncilsSet instanceof Set && artistDistrictsSet instanceof Set) {
    return true;
  }

  if (!artistCoverageLoadPromise) {
    artistCoverageLoadPromise = (async () => {
      const response = await fetch("api/get_artists.php?summary=locations");

      if (!response.ok) {
        throw new Error(
          `Erro de Rede ${response.status}: ${response.statusText}`,
        );
      }

      const data = await response.json();

      if (data?.error) {
        throw new Error(data.error);
      }

      const councils = Array.isArray(data?.councils) ? data.councils : [];
      const districts = Array.isArray(data?.districts) ? data.districts : [];

      artistCouncilsSet = new Set(
        councils.map((name) => normalizeText(name)).filter(Boolean),
      );
      artistDistrictsSet = new Set(
        districts.map((name) => normalizeDistrictName(name)).filter(Boolean),
      );

      buildDistrictCoverageFromCouncils();
      return true;
    })().catch((error) => {
      artistCoverageLoadPromise = null;
      throw error;
    });
  }

  await artistCoverageLoadPromise;
  return true;
}

// Função utilitária: verifica se um ponto está dentro de um GeoJSON
function isPointInGeoJSON(geojson, lat, lng) {
  if (!geojson || !geojson.coordinates) return false;

  const pointInRing = (x, y, ring) => {
    let inside = false;
    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
      const xi = ring[i][0],
        yi = ring[i][1];
      const xj = ring[j][0],
        yj = ring[j][1];

      const intersect =
        yi > y !== yj > y && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi;
      if (intersect) inside = !inside;
    }
    return inside;
  };

  if (geojson.type === "Polygon") {
    const rings = geojson.coordinates;
    if (pointInRing(lng, lat, rings[0])) return true;
  } else if (geojson.type === "MultiPolygon") {
    for (const poly of geojson.coordinates) {
      const rings = poly;
      if (pointInRing(lng, lat, rings[0])) return true;
    }
  }

  return false;
}

// Função para verificar se um ponto [lng, lat] está dentro de uma geometria GeoJSON
function isPointInPolygon(point, geometry) {
  if (!geometry) return false;

  const pointInRing = (x, y, ring) => {
    let inside = false;
    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
      const xi = ring[i][0],
        yi = ring[i][1];
      const xj = ring[j][0],
        yj = ring[j][1];

      const intersect =
        yi > y !== yj > y && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi;
      if (intersect) inside = !inside;
    }
    return inside;
  };

  const [lng, lat] = point;

  if (geometry.type === "Polygon") {
    const rings = geometry.coordinates;
    if (pointInRing(lng, lat, rings[0])) return true;
  } else if (geometry.type === "MultiPolygon") {
    for (const poly of geometry.coordinates) {
      const rings = poly;
      if (pointInRing(lng, lat, rings[0])) return true;
    }
  }

  return false;
}

// Calcula a área (valor absoluto) de um anel (ring) GeoJSON (coord: [lng, lat])
function polygonArea(ring) {
  let area = 0;
  for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
    const xi = ring[i][0],
      yi = ring[i][1];
    const xj = ring[j][0],
      yj = ring[j][1];
    area += xi * yj - xj * yi;
  }
  return Math.abs(area) / 2;
}

// Calcula o centróide de um anel (ring) GeoJSON (coord: [lng, lat])
// Retorna [lat, lng]
function polygonCentroid(ring) {
  let A = 0,
    Cx = 0,
    Cy = 0;
  for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
    const xi = ring[i][0],
      yi = ring[i][1];
    const xj = ring[j][0],
      yj = ring[j][1];
    const factor = xi * yj - xj * yi;
    A += factor;
    Cx += (xi + xj) * factor;
    Cy += (yi + yj) * factor;
  }
  A = A / 2;
  if (A === 0) {
    // Fallback: usar o primeiro ponto
    return [ring[0][1], ring[0][0]];
  }
  const cx = Cx / (6 * A); // longitude
  const cy = Cy / (6 * A); // latitude
  return [cy, cx];
}

// Calcula centróide para uma geometria GeoJSON (Polygon/MultiPolygon)
function computeFeatureCentroid(geometry) {
  if (!geometry) return null;
  if (geometry.type === "Polygon") {
    const outer = geometry.coordinates[0];
    return polygonCentroid(outer);
  }
  if (geometry.type === "MultiPolygon") {
    let totalArea = 0;
    let latSum = 0;
    let lngSum = 0;
    for (const poly of geometry.coordinates) {
      const outer = poly[0];
      const a = polygonArea(outer);
      if (a === 0) continue;
      const c = polygonCentroid(outer); // [lat, lng]
      latSum += c[0] * a;
      lngSum += c[1] * a;
      totalArea += a;
    }
    if (totalArea === 0) return null;
    return [latSum / totalArea, lngSum / totalArea];
  }
  return null;
}

// Função para criar um marcador especial
function createSelectedCityMarker(city) {
  const selectedIcon = L.divIcon({
    className: "selected-city-marker",
    html: "📍",
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40],
  });

  return L.marker([city.lat, city.lng], {
    icon: selectedIcon,
    zIndexOffset: 1000,
  });
}

// ============================================
// FUNÇÕES DE MUNICÍPIOS PORTUGUESES
// ============================================

// Constante de cores para otimização (definida fora do loop)
const CORES_DISTRITOS = {
  Lisboa: "#4a148c",
  Porto: "#1a237e",
  Aveiro: "#0d47a1",
  Braga: "#1565c0",
  Bragança: "#3949ab",
  Coimbra: "#0277bd",
  Setúbal: "#00838f",
  Faro: "#00695c",
  Leiria: "#5d4037",
  Santarém: "#6a1b9a",
  "Viana do Castelo": "#283593",
  "Vila Real": "#1565c0",
  Viseu: "#0277bd",
  Guarda: "#00838f",
  "Castelo Branco": "#00695c",
  Portalegre: "#2e7d32",
  Évora: "#558b2f",
  Beja: "#5d4037",
};

// Cores mais vibrantes para modo escuro
const CORES_DISTRITOS_DARK = {
  Lisboa: "#9c27b0",
  Porto: "#5c6bc0",
  Aveiro: "#2196f3",
  Braga: "#42a5f5",
  Bragança: "#5c6bc0",
  Coimbra: "#29b6f6",
  Setúbal: "#26c6da",
  Faro: "#26a69a",
  Leiria: "#a1887f",
  Santarém: "#ce93d8",
  "Viana do Castelo": "#7986cb",
  "Vila Real": "#42a5f5",
  Viseu: "#29b6f6",
  Guarda: "#26c6da",
  "Castelo Branco": "#26a69a",
  Portalegre: "#66bb6a",
  Évora: "#9ccc65",
  Beja: "#a1887f",
};

// Função para obter as cores corretas baseado no tema
function getCoresDistritosAtual() {
  return CORES_DISTRITOS_DARK;
}

function normalizeDistrictName(name) {
  return (name || "")
    .toString()
    .trim()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
}

function getDistrictColorByName(name, fallbackColor = "#757575") {
  const coresAtuais = getCoresDistritosAtual();
  const normalizedTarget = normalizeDistrictName(name);

  if (!normalizedTarget) {
    return fallbackColor;
  }

  for (const [districtName, color] of Object.entries(coresAtuais)) {
    if (normalizeDistrictName(districtName) === normalizedTarget) {
      return color;
    }
  }

  return fallbackColor;
}

// Função para atualizar a legenda com as cores dos distritos
function updateLegend() {
  if (!municipiosLegend) return;

  municipiosLegend.innerHTML = "<h4>Distritos</h4>";

  const coresAtuais = getCoresDistritosAtual();
  const distritosFromData = (distritosDataCache?.features || [])
    .map(
      (feature) => feature?.properties?.distrito || feature?.properties?.name,
    )
    .filter(Boolean);

  const distritos = Array.from(
    new Set([...Object.keys(coresAtuais), ...distritosFromData]),
  ).sort((a, b) => a.localeCompare(b, "pt"));

  distritos.forEach((distrito) => {
    const cor = getDistrictColorByName(distrito, "#757575");
    const item = document.createElement("div");
    item.className = "legend-item";
    item.innerHTML = `<div class="legend-color" style="background-color: ${cor}"></div><span>${distrito}</span>`;
    municipiosLegend.appendChild(item);
  });
}

// Função para carregar municípios portugueses
async function carregarMunicipiosPortugueses() {
  try {
    console.log("🗺️ Carregando concelhos portugueses...");

    const response = await fetch("data/municipios_pt.geojson");
    municipiosData = await response.json();

    // Filtrar municípios para incluir apenas o Continente (excluir Madeira e Açores)
    const normalize = (s) =>
      (s || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");

    if (municipiosData && municipiosData.features) {
      municipiosData.features = municipiosData.features.filter((f) => {
        const distrito = normalize(
          f.properties.distrito_ilha || f.properties.distrito || "",
        );
        if (!distrito) return true;
        if (distrito.includes("madeira")) return false;
        if (distrito.includes("acores") || distrito.includes("açores"))
          return false;
        return true;
      });
    }

    console.log(
      `✅ ${municipiosData.features.length} concelhos carregados (Continente)`,
    );

    // Criar camada dos municípios
    municipiosLayer = L.geoJSON(municipiosData, {
      style: function (feature) {
        // Usar cores dos distritos para dar vida ao mapa
        const distrito = feature.properties.distrito_ilha || "Desconhecido";
        const cor = getDistrictColorByName(distrito, "#757575");

        return {
          fillColor: cor,
          fillOpacity: 0.15, // Cor suave para ver o mapa por baixo
          color: cor,
          weight: 1,
          opacity: 0.8,
          className: "municipio-layer",
        };
      },
      onEachFeature: function (feature, layer) {
        // Adicionar etiqueta com o nome
        if (feature.properties && feature.properties.municipio) {
          layer.bindTooltip(feature.properties.municipio, {
            permanent: true,
            direction: "center",
            className: "municipio-label",
          });

          // Posicionar o tooltip no centróide real do polígono
          try {
            const centroid = computeFeatureCentroid(feature.geometry);
            const tooltip = layer.getTooltip();
            if (centroid && tooltip) {
              tooltip.setLatLng(centroid);
            }
          } catch (err) {
            // Se falhar, manter comportamento por omissão
            console.warn(
              "Não foi possível calcular centróide do município:",
              err,
            );
          }

          // Inicialmente ocultar o rótulo se o zoom for muito baixo
          if (map.getZoom() < MIN_ZOOM_FOR_LABELS) {
            layer.closeTooltip();
          }
        }

        // Eventos de interação
        layer.on({
          mouseover: function (e) {
            if (!layer._selected) {
              layer.setStyle({
                fillOpacity: 0.4, // Realçar cor ao passar o rato
                weight: 2,
              });
            }
            if (map.getZoom() < MIN_ZOOM_FOR_LABELS) {
              layer.openTooltip();
            }
            // layer.openPopup(); // Removido
          },
          mouseout: function (e) {
            if (!layer._selected) {
              municipiosLayer.resetStyle(layer);
            }
            if (map.getZoom() < MIN_ZOOM_FOR_LABELS) {
              layer.closeTooltip();
            }
            // layer.closePopup(); // Removido
          },
          click: function (e) {
            selecionarMunicipio(feature, layer);
            e.originalEvent.preventDefault();
            e.originalEvent.stopPropagation();
          },
        });
      },
    });

    console.log("✅ Camada de concelhos criada com sucesso!");
    return municipiosLayer;
  } catch (error) {
    console.error("❌ Erro ao carregar concelhos:", error);
    showTempMessage("Erro ao carregar concelhos", "error");
    return null;
  }
}

// Função para selecionar um município
function selecionarMunicipio(feature, layer) {
  closeArtistModal();

  if (!sidebar.classList.contains("open")) {
    sidebar.classList.add("open");
  }

  // Se existir um distrito selecionado, desmarcar para manter estado consistente
  if (distritoSelecionado && distritosLayer) {
    distritoSelecionado._selected = false;
    distritosLayer.resetStyle(distritoSelecionado);
    distritoSelecionado = null;
  }

  // Se o município clicado já está selecionado, desmarca-o e pára.
  if (layer._selected) {
    layer._selected = false;
    municipiosLayer.resetStyle(layer);
    municipioSelecionado = null;
    infoBox.style.display = "none";
    infoBoxVisible = false;
    artistDetailsPanel.style.display = "none";
    sidebar.classList.remove("open");
    return;
  }

  // Se outro município estava selecionado, desmarca-o.
  if (municipioSelecionado) {
    municipioSelecionado._selected = false;
    municipiosLayer.resetStyle(municipioSelecionado);
  }

  // Seleciona o novo município
  layer._selected = true;

  // ADICIONE ESTA LINHA AQUI:
  layer.bringToFront(); // <--- Traz o município para a frente de tudo

  layer.setStyle({
    fillOpacity: 0.4,
    weight: 3,
    opacity: 1,
    className: "municipio-layer municipio-highlight",
  });
  municipioSelecionado = layer;

  // Centralizar no município
  const bounds = layer.getBounds();
  animateToLayerBounds(bounds, {
    padding: [50, 50],
    maxZoom: 11,
  });

  // Mostrar informações
  const props = feature.properties;
  const centro = bounds.getCenter();

  const city = {
    name: props.municipio || "Concelho",
    lat: centro.lat,
    lng: centro.lng,
    countryName: "Portugal",
    adminName1: props.distrito_ilha || "",
    type: "municipality",
    properties: props,
    source: "dgt",
  };

  if (selfMapFocusSortResetPending) {
    const selectedCouncil = normalizeText(city.name);
    const ownCouncil = normalizeText(selfMapFocusCouncilName);

    if (selectedCouncil && ownCouncil && selectedCouncil !== ownCouncil) {
      selfMapFocusSortResetPending = false;
      selfMapFocusCouncilName = "";

      if (currentArtistSort !== "aleatorio") {
        currentArtistSort = "aleatorio";
        if (artistSortInput) {
          artistSortInput.value = currentArtistSort;
        }
      }
    }
  }

  // Resetar vistas
  artistDetailsPanel.style.display = "none";

  // --- MODO "SÓ ARTISTAS" ---
  // Definir apenas o nome e esconder o resto para focar nos artistas
  cityName.textContent = city.name;
  cityImageContainer.style.display = "none";
  cityDetails.style.display = "none"; // Esconde detalhes técnicos
  document.querySelector(".coordinates").style.display = "none"; // Esconde coordenadas

  infoBox.style.display = "block";
  infoBoxVisible = true;

  // Adicionar marcador no centroide
  if (currentMarker) {
    map.removeLayer(currentMarker);
    currentMarker = null;
  }

  // Remover contorno de cidade anterior
  if (currentCityBoundary) {
    map.removeLayer(currentCityBoundary);
    currentCityBoundary = null;
  }

  lastBoundarySource = "dgt";

  // Carregar artistas deste município
  loadArtistsForCouncil(city.name);

  showTempMessage(`Concelho "${city.name}" selecionado ✓`, "success");
}

// Busca por município por nome
async function buscarMunicipioPorNome(nome) {
  try {
    const response = await fetch("data/municipios_index.json");
    const municipios = await response.json();

    const nomeNormalizado = nome
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");

    return municipios.filter((m) => {
      const nomeMunicipio = (m.nome || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
      const distrito = (m.distrito || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");

      return (
        nomeMunicipio.includes(nomeNormalizado) ||
        nomeNormalizado.includes(nomeMunicipio) ||
        distrito.includes(nomeNormalizado)
      );
    });
  } catch (error) {
    console.error("Erro na busca de concelhos:", error);
    return [];
  }
}

async function buscarDistritoPorNome(nome) {
  try {
    const response = await fetch("data/distritos_index.json");
    const distritos = await response.json();

    const nomeNormalizado = normalizeDistrictName(nome);

    return distritos.filter((d) => {
      const nomeDistrito = normalizeDistrictName(d.nome || "");

      return (
        nomeDistrito.includes(nomeNormalizado) ||
        nomeNormalizado.includes(nomeDistrito)
      );
    });
  } catch (error) {
    console.error("Erro na busca de distritos:", error);
    return [];
  }
}

// ============================================
// BUSCA DE CIDADES (ORIGINAL + MUNICÍPIOS)
// ============================================

// Função para buscar cidades na API Nominatim
async function searchCities(query) {
  const normalizedQuery = (query || "").trim();
  const isGenreMode = getEffectiveSearchMode() === "genre";
  const isDistritosMode = getEffectiveLayerType() === "distritos";

  if (!normalizedQuery || normalizedQuery.length < 2) {
    activeSearchResultIndex = -1;
    resultsContainer.style.display = "none";
    if (searchHelper) {
      searchHelper.textContent = getMinSearchMessage();
    }
    return;
  }

  if (isGenreMode) {
    await searchGenres(normalizedQuery);
    return;
  }

  showLoading(true);
  if (searchHelper) {
    searchHelper.textContent = "A procurar resultados...";
  }

  if (isDistritosMode) {
    const distritosEncontrados = await buscarDistritoPorNome(normalizedQuery);

    if (distritosEncontrados.length > 0) {
      const resultadosFormatados = distritosEncontrados.slice(0, 7).map((d) => {
        return {
          name: d.nome || "Distrito",
          countryName: "Portugal",
          adminName1: "Portugal",
          type: "district",
          source: "distritos_index",
          codigo: `${d.codigo_distrito ?? ""}`,
        };
      });

      displayResults(resultadosFormatados);
      if (searchHelper) {
        searchHelper.textContent = `${resultadosFormatados.length} resultado(s) encontrado(s). Usa Enter para abrir o primeiro.`;
      }
      showLoading(false);
      return;
    }

    showLoading(false);
    activeSearchResultIndex = -1;
    resultsContainer.innerHTML =
      '<div class="result-item">Nenhum distrito encontrado</div>';
    resultsContainer.style.display = "block";
    if (searchHelper) {
      searchHelper.textContent =
        "Não encontrámos resultados. Tenta outro nome de distrito.";
    }
    return;
  }

  // Primeiro, buscar nos municípios locais
  const municipiosEncontrados = await buscarMunicipioPorNome(normalizedQuery);

  if (municipiosEncontrados.length > 0) {
    const resultadosFormatados = municipiosEncontrados.slice(0, 5).map((m) => {
      return {
        name: m.nome || "Concelho",
        lat: m.centroide_lat || 0,
        lng: m.centroide_lng || 0,
        countryName: "Portugal",
        adminName1: m.distrito || "",
        type: "municipality",
        source: "dgt",
        codigo: m.codigo_dtmn || m.dtmn,
        area: m.area_km2 ? `${m.area_km2} km²` : "",
      };
    });

    displayResults(resultadosFormatados);
    if (searchHelper) {
      searchHelper.textContent = `${resultadosFormatados.length} resultado(s) encontrado(s). Usa Enter para abrir o primeiro.`;
    }
    showLoading(false);
    return;
  }

  // Se não encontrar nos dados locais, usar Nominatim
  try {
    const response = await makeNominatimRequest(
      `${NOMINATIM_URL}/search?q=${encodeURIComponent(
        normalizedQuery,
      )}+Portugal&format=json&polygon_geojson=1&addressdetails=1&limit=7`,
    );

    const data = await response.json();
    showLoading(false);

    if (data && data.length > 0) {
      const cities = data.map((result) => ({
        name: result.name || result.display_name.split(",")[0],
        lat: parseFloat(result.lat),
        lng: parseFloat(result.lon),
        countryName: result.address?.country || "Portugal",
        adminName1:
          result.address?.state ||
          result.address?.region ||
          result.address?.county ||
          "",
        population: 0,
        display_name: result.display_name,
        osm_id: result.osm_id,
        osm_type: result.osm_type,
        type: result.type,
        class: result.class,
      }));
      displayResults(cities);
      if (searchHelper) {
        searchHelper.textContent = `${cities.length} resultado(s) encontrado(s).`;
      }
    } else {
      activeSearchResultIndex = -1;
      resultsContainer.innerHTML =
        '<div class="result-item">Nenhuma cidade encontrada</div>';
      resultsContainer.style.display = "block";
      if (searchHelper) {
        searchHelper.textContent =
          "Não encontrámos resultados. Tenta outro nome de cidade/concelho.";
      }
    }
  } catch (error) {
    showLoading(false);
    console.error("Erro ao buscar cidades:", error);
    activeSearchResultIndex = -1;
    resultsContainer.innerHTML =
      '<div class="result-item">Erro ao buscar cidades</div>';
    resultsContainer.style.display = "block";
    if (searchHelper) {
      searchHelper.textContent =
        "Não foi possível pesquisar agora. Tenta novamente em instantes.";
    }
  }
}

// Função para exibir resultados da busca
function displayResults(cities) {
  resultsContainer.innerHTML = "";
  activeSearchResultIndex = -1;

  cities.forEach((city, index) => {
    const resultItem = document.createElement("div");
    resultItem.className = "result-item";
    resultItem.setAttribute("role", "option");
    resultItem.setAttribute("aria-selected", "false");
    resultItem.setAttribute("tabindex", "-1");
    resultItem.dataset.resultIndex = index;

    if (city.source === "dgt") {
      // Resultado de município DGT
      const distrito = city.adminName1 ? ` • ${city.adminName1}` : "";
      resultItem.innerHTML = `
                <div class="result-name">${city.name}</div>
            <div class="result-details">Concelho${distrito}</div>
            `;
    } else if (city.source === "distritos_index") {
      resultItem.innerHTML = `
                <div class="result-name">${city.name}</div>
            <div class="result-details">Distrito</div>
            `;
    } else if (city.source === "genre_index") {
      const totalArtists = Number(city.artistCount || 0);
      const detailsLabel =
        totalArtists > 0
          ? `${totalArtists} artista(s) • Portugal`
          : "Género • Portugal";

      resultItem.innerHTML = `
                <div class="result-name">${city.name}</div>
            <div class="result-details">${detailsLabel}</div>
            `;
    } else {
      // Resultado do Nominatim
      const country = city.countryName || "Portugal";
      const adminArea = city.adminName1 ? `, ${city.adminName1}` : "";
      const typeLabel =
        city.type === "city"
          ? "Cidade"
          : city.type === "town"
            ? "Vila"
            : city.type === "village"
              ? "Aldeia"
              : city.type === "municipality"
                ? "Concelho"
                : "Local";

      resultItem.innerHTML = `
                <div class="result-name">${city.name}${adminArea}</div>
                <div class="result-details">${typeLabel} • ${country}</div>
            `;
    }

    resultItem.addEventListener("click", () => {
      selectResultCity(city);
    });

    resultItem.addEventListener("mouseenter", () => {
      setActiveSearchResult(index);
    });

    resultsContainer.appendChild(resultItem);
  });

  if (resultsContainer.children.length > 0) {
    resultsContainer.style.display = "block";
    setActiveSearchResult(0);
  } else {
    resultsContainer.innerHTML =
      '<div class="result-item">Nenhum resultado encontrado</div>';
    resultsContainer.style.display = "block";
  }
}

function getVisibleSearchItems() {
  if (resultsContainer.style.display === "none") return [];
  return Array.from(
    resultsContainer.querySelectorAll(".result-item[data-result-index]"),
  );
}

function setActiveSearchResult(index) {
  const items = getVisibleSearchItems();
  if (!items.length) {
    activeSearchResultIndex = -1;
    return;
  }

  const safeIndex = Math.max(0, Math.min(index, items.length - 1));
  activeSearchResultIndex = safeIndex;

  items.forEach((item, itemIndex) => {
    const isActive = itemIndex === safeIndex;
    item.classList.toggle("active", isActive);
    item.setAttribute("aria-selected", isActive ? "true" : "false");
  });

  items[safeIndex].scrollIntoView({ block: "nearest" });
}

function clearSearchUI() {
  cityInput.value = "";
  resultsContainer.style.display = "none";
  activeSearchResultIndex = -1;
  if (clearSearchBtn) {
    clearSearchBtn.classList.remove("visible");
  }
  if (searchHelper) {
    searchHelper.textContent = getMinSearchMessage();
  }
}

// Nova função para selecionar cidade dos resultados
async function selectResultCity(city) {
  if (city.source === "genre_index") {
    showGenreInfo(city.name || "Género");
  } else if (city.source === "distritos_index") {
    await loadDistritos();

    if (distritosLayer) {
      let matched = false;

      distritosLayer.eachLayer(function (layer) {
        if (matched) {
          return;
        }

        const layerName = normalizeDistrictName(
          getFeatureDistritoName(layer.feature),
        );
        const cityName = normalizeDistrictName(city.name || "");
        const layerCode = `${layer.feature?.properties?.codigo_distrito ?? ""}`;

        if (layerName === cityName || layerCode === `${city.codigo ?? ""}`) {
          selecionarDistrito(layer.feature, layer);
          matched = true;
        }
      });
    }
  } else if (city.source === "dgt") {
    // Buscar o município na camada
    if (municipiosLayer) {
      municipiosLayer.eachLayer(function (layer) {
        const layerProps = layer.feature.properties;
        const layerName = (layerProps.municipio || "")
          .toLowerCase()
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "");
        const cityName = (city.name || "")
          .toLowerCase()
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "");

        if (layerName === cityName || layerProps.codigo_dtmn === city.codigo) {
          selecionarMunicipio(layer.feature, layer);
        }
      });
    }
  } else {
    selectCity(city);
  }

  resultsContainer.style.display = "none";
  activeSearchResultIndex = -1;
  if (searchHelper) {
    searchHelper.textContent = `Selecionado: ${city.name}`;
  }
}

// ============================================
// FUNÇÕES ORIGINAIS DO MAPA
// ============================================

// Função para buscar limites da cidade usando Nominatim
async function getCityBoundary(city) {
  try {
    let nominatimUrl;

    // Se temos OSM ID, buscar pelo ID (mais preciso)
    if (city.osm_id && city.osm_type) {
      const osmType = city.osm_type.charAt(0).toUpperCase();
      nominatimUrl = `${NOMINATIM_URL}/lookup?osm_ids=${osmType}${city.osm_id}&format=geojson&polygon_geojson=1`;
    } else {
      // Buscar pelo nome
      const searchQuery = `${city.name}, Portugal`;
      nominatimUrl = `${NOMINATIM_URL}/search?q=${encodeURIComponent(
        searchQuery,
      )}&format=geojson&polygon_geojson=1&limit=1`;
    }

    const response = await makeNominatimRequest(nominatimUrl);

    if (response.ok) {
      const data = await response.json();

      if (data.features && data.features.length > 0) {
        const feature = data.features[0];

        // Verificar se é um polígono válido
        if (
          feature.geometry &&
          (feature.geometry.type === "Polygon" ||
            feature.geometry.type === "MultiPolygon")
        ) {
          return {
            layer: L.geoJSON(feature.geometry, {
              style: {
                fillColor: "#9c27b0",
                fillOpacity: 0.25,
                color: "#4a148c",
                weight: 3,
                className: "city-boundary",
              },
              interactive: false,
            }),
            source: "nominatim",
          };
        }
      }
    }

    return null;
  } catch (error) {
    console.error("Erro ao buscar limites:", error);
    return null;
  }
}

// Função para selecionar uma cidade (original)
async function selectCity(city) {
  resultsContainer.style.display = "none";
  cityInput.value = "";

  if (!sidebar.classList.contains("open")) {
    sidebar.classList.add("open");
  }

  // Verificar se a cidade está dentro de Portugal
  if (
    portugalGeoJSON &&
    !isPointInGeoJSON(
      portugalGeoJSON,
      parseFloat(city.lat),
      parseFloat(city.lng),
    )
  ) {
    showTempMessage("Apenas cidades em Portugal são permitidas", "warning");
    return;
  }

  // Não adicionar marcador visual; apenas remover qualquer marcador anterior
  if (currentMarker) {
    map.removeLayer(currentMarker);
    currentMarker = null;
  }

  // Mostrar informações
  showCityInfo(city);
  infoBox.style.display = "block";
  artistDetailsPanel.style.display = "none"; // Garantir que detalhes estão fechados
  councilArtistsSection.style.display = "none"; // Esconder lista até carregar (se aplicável)

  infoBoxVisible = true;

  // Remover contorno anterior
  if (currentCityBoundary) {
    map.removeLayer(currentCityBoundary);
    currentCityBoundary = null;
  }

  // Remover destaque de município se houver
  if (municipiosLayer) {
    municipiosLayer.eachLayer(function (l) {
      if (l._selected) {
        municipiosLayer.resetStyle(l);
        l._selected = false;
      }
    });
    municipioSelecionado = null;
  }

  // Adicionar marcador simples (já que removemos os contornos não-DGT)
  if (currentMarker) {
    map.removeLayer(currentMarker);
  }
  currentMarker = L.marker([city.lat, city.lng]).addTo(map);
  currentMarker
    .bindPopup(`<b>${city.name}</b><br>${city.countryName || ""}`)
    .openPopup();

  // Tentar carregar artistas se for um município válido
  if (city.type === "municipality" || city.type === "city") {
    loadArtistsForCouncil(city.name);
  }

  map.setView([city.lat, city.lng], 14);
}

// Função para mostrar informações da cidade
function showCityInfo(city) {
  cityName.textContent = city.name;

  // Garantir que os detalhes estão visíveis (caso tenham sido ocultos pelo modo "só artistas")
  cityDetails.style.display = "block";
  document.querySelector(".coordinates").style.display = "flex";

  // Buscar e mostrar imagem da cidade
  fetchCityImage(city.name);

  let details = "";

  if (city.source === "dgt") {
    // Informações de município DGT
    details += `<strong>Fonte:</strong> DGT (Dados Oficiais)<br>`;
    if (city.adminName1)
      details += `<strong>Distrito:</strong> ${city.adminName1}<br>`;

    if (city.properties) {
      const props = city.properties;
      if (props.codigo_dtmn || props.dtmn)
        details += `<strong>Código DTMN:</strong> ${
          props.codigo_dtmn || props.dtmn
        }<br>`;
      if (props.nuts3)
        details += `<strong>NUTS III:</strong> ${props.nuts3}<br>`;
      if (props.area_ha)
        details += `<strong>Área:</strong> ${(props.area_ha / 100).toFixed(
          1,
        )} km²<br>`;
      if (props.n_freguesias)
        details += `<strong>Freguesias:</strong> ${props.n_freguesias}<br>`;
    }
  } else {
    // Informações do Nominatim
    if (city.adminName1)
      details += `<strong>Região/Estado:</strong> ${city.adminName1}<br>`;
    if (city.countryName)
      details += `<strong>País:</strong> ${city.countryName}<br>`;
    if (city.type) {
      const typeLabel =
        city.type === "city"
          ? "Cidade"
          : city.type === "town"
            ? "Vila"
            : city.type === "village"
              ? "Aldeia"
              : city.type === "municipality"
                ? "Concelho"
                : city.type;
      details += `<strong>Tipo:</strong> ${typeLabel}<br>`;
    }
    if (city.display_name) {
      details += `<strong>Nome completo:</strong> ${city.display_name
        .split(",")
        .slice(0, 3)
        .join(",")}<br>`;
    }
  }

  cityDetails.innerHTML = details || "Informações não disponíveis";
  cityLat.textContent = `Latitude: ${parseFloat(city.lat).toFixed(6)}`;
  cityLng.textContent = `Longitude: ${parseFloat(city.lng).toFixed(6)}`;

  // Mostrar a fonte dos limites
  if (lastBoundarySource) {
    cityDetails.innerHTML += `<br><small>Fonte dos limites: ${
      lastBoundarySource === "dgt"
        ? "DGT (Oficial)"
        : lastBoundarySource === "nominatim"
          ? "OpenStreetMap"
          : "Estimado"
    }</small>`;
  }
}

// ============================================
// LÓGICA DE ARTISTAS
// ============================================

function normalizeArtistSortSelection(value) {
  const normalized = normalizeText(value);

  if (normalized === "popularidade") {
    return "popularidade";
  }

  if (normalized === "recentes") {
    return "recentes";
  }

  if (normalized === "aleatorio") {
    return "aleatorio";
  }

  return "aleatorio";
}

function splitArtistGenresValue(value) {
  const raw = (value || "").toString().trim();
  if (!raw) {
    return [];
  }

  return raw
    .split(/\s*[,;|/]\s*/)
    .map((genre) => genre.trim())
    .filter(Boolean);
}

function getArtistRecencyValue(artist) {
  const candidates = [
    artist?.created_at,
    artist?.createdAt,
    artist?.registered_at,
    artist?.registration_date,
    artist?.updated_at,
  ];

  for (const value of candidates) {
    if (!value) {
      continue;
    }

    const date = new Date(value);
    const timestamp = date.getTime();
    if (Number.isFinite(timestamp)) {
      return timestamp;
    }
  }

  const fallbackId = Number(artist?.id || 0);
  return Number.isFinite(fallbackId) ? fallbackId : 0;
}

function shuffleArtists(items) {
  const shuffled = [...items];
  for (let index = shuffled.length - 1; index > 0; index -= 1) {
    const randomIndex = Math.floor(Math.random() * (index + 1));
    [shuffled[index], shuffled[randomIndex]] = [
      shuffled[randomIndex],
      shuffled[index],
    ];
  }
  return shuffled;
}

function shouldApplyAllGenres(value) {
  const normalized = normalizeText(value);
  return normalized === "" || normalized === "todos os generos";
}

function updateArtistGenreClearButton() {
  if (!artistGenreInput || !artistGenreClearBtn) {
    return;
  }

  const hasSelectedGenre = !shouldApplyAllGenres(artistGenreInput.value || "");
  artistGenreClearBtn.hidden = !hasSelectedGenre;

  if (artistGenreField) {
    artistGenreField.classList.toggle("has-value", hasSelectedGenre);
  }
}

function hideArtistGenreDropdown() {
  if (artistGenreDropdown) {
    artistGenreDropdown.hidden = true;
  }
}

function showArtistGenreDropdown() {
  if (artistGenreDropdown) {
    artistGenreDropdown.hidden = false;
  }
}

function renderArtistGenreDropdownOptions(query = "") {
  if (!artistGenreDropdownList || !artistGenreDropdownEmpty) {
    return;
  }

  const normalizedQuery = normalizeText(query);
  const available = Array.isArray(artistGenreOptionsList)
    ? artistGenreOptionsList
    : [];

  const filtered = available.filter((optionValue) => {
    if (!normalizedQuery) {
      return true;
    }

    return normalizeText(optionValue).includes(normalizedQuery);
  });

  artistGenreDropdownList.innerHTML = "";

  filtered.forEach((optionValue) => {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "artist-genre-dropdown-item";
    button.textContent = optionValue;

    button.addEventListener("mousedown", (event) => {
      event.preventDefault();
    });

    button.addEventListener("click", () => {
      const isAllGenres = shouldApplyAllGenres(optionValue);
      const selectedValue = isAllGenres ? "" : optionValue;

      if (artistGenreInput) {
        artistGenreInput.value = selectedValue;
      }

      currentArtistGenreFilter = selectedValue;
      applyArtistFiltersAndRender();
      updateArtistGenreClearButton();
      hideArtistGenreDropdown();
    });

    artistGenreDropdownList.appendChild(button);
  });

  const hasResults = filtered.length > 0;
  artistGenreDropdownEmpty.hidden = hasResults;
}

function populateArtistGenreCombobox(artists, resetSelection = true) {
  if (!artistGenreInput) {
    return;
  }

  const fallbackGenres = new Set();
  (Array.isArray(artists) ? artists : []).forEach((artist) => {
    splitArtistGenresValue(artist?.genre).forEach((genre) => {
      fallbackGenres.add(genre);
    });
  });

  const sortedGenres = Array.from(fallbackGenres).sort((a, b) =>
    a.localeCompare(b, "pt", { sensitivity: "base" }),
  );

  artistGenreOptionsList = ["Todos os géneros", ...sortedGenres];
  renderArtistGenreDropdownOptions(artistGenreInput.value || "");

  if (resetSelection) {
    currentArtistGenreFilter = "";
    if (artistGenreInput) {
      artistGenreInput.value = "";
    }
  }

  updateArtistGenreClearButton();
}

function getFilteredAndSortedArtists(artists) {
  const source = Array.isArray(artists) ? artists : [];
  const normalizedGenreFilter = shouldApplyAllGenres(currentArtistGenreFilter)
    ? ""
    : normalizeText(currentArtistGenreFilter);

  let filtered = source;

  if (normalizedGenreFilter) {
    filtered = source.filter((artist) => {
      const genres = splitArtistGenresValue(artist?.genre);
      return genres.some((genre) =>
        normalizeText(genre).includes(normalizedGenreFilter),
      );
    });
  }

  if (currentArtistSort === "recentes") {
    return [...filtered].sort(
      (artistA, artistB) =>
        getArtistRecencyValue(artistB) - getArtistRecencyValue(artistA),
    );
  }

  if (currentArtistSort === "aleatorio") {
    return shuffleArtists(filtered);
  }

  return [...filtered].sort(
    (artistA, artistB) =>
      normalizeArtistUpvotes(artistB) - normalizeArtistUpvotes(artistA),
  );
}

function applyArtistFiltersAndRender() {
  const filteredArtists = getFilteredAndSortedArtists(currentArtistsRaw);
  renderArtistsList(filteredArtists);
}

function setArtistsForCurrentContext(
  artists,
  { resetGenreFilter = true, showGenreSidebarFilter = true } = {},
) {
  currentArtistsRaw = Array.isArray(artists) ? artists : [];

  if (artistsFilters) {
    const isGenreMode = getEffectiveSearchMode() === "genre";
    artistsFilters.style.display =
      currentArtistsRaw.length > 0 || isGenreMode ? "grid" : "none";
  }

  if (artistGenreFilterGroup) {
    artistGenreFilterGroup.style.display = showGenreSidebarFilter
      ? "flex"
      : "none";
  }

  if (artistSortInput) {
    artistSortInput.value = currentArtistSort;
  }

  populateArtistGenreCombobox(currentArtistsRaw, resetGenreFilter);
  applyArtistFiltersAndRender();
}

function syncArtistSortFromInput() {
  if (!artistSortInput) {
    return;
  }

  const sortSelection = normalizeArtistSortSelection(artistSortInput.value);

  if (currentArtistSort !== sortSelection) {
    currentArtistSort = sortSelection;
    applyArtistFiltersAndRender();
  }
}

// Função para carregar artistas do concelho
async function loadArtistsForCouncil(councilName) {
  councilArtistsSection.style.display = "block";
  if (artistsFilters) {
    artistsFilters.style.display = "none";
  }
  artistsList.innerHTML =
    '<div style="padding:10px; color: var(--text-muted);">A carregar artistas...</div>';

  console.log("🔍 A procurar artistas para:", councilName);

  try {
    // NOTA: Certifique-se que este endpoint existe e retorna JSON
    // Exemplo de URL: api/get_artists.php?council=Lisboa
    const response = await fetch(
      `api/get_artists.php?council=${encodeURIComponent(councilName)}`,
      { cache: "no-store" },
    );

    // Se a resposta da rede não for OK (ex: 404, 500), trata o erro.
    if (!response.ok) {
      const errorText = await response.text(); // Tenta ler o corpo do erro
      console.error(
        "❌ Erro na API:",
        response.status,
        response.statusText,
        errorText,
      );
      artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Erro de Rede ${response.status}:</strong> ${response.statusText}.</div>`;
      return;
    }

    const data = await response.json();

    // Verificar se a API retornou um erro na sua resposta JSON
    if (data.error) {
      console.error("❌ Erro da Base de Dados:", data.error);
      artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Erro no Servidor:</strong> ${data.error}</div>`;
      return;
    }

    const artists = Array.isArray(data) ? data : [];
    console.log("📦 Artistas recebidos:", artists);

    if (artists.length === 0) {
      console.warn(
        `⚠️ Nenhum artista encontrado. Verifique se na BD o concelho contém "${councilName}"`,
      );
    }
    setArtistsForCurrentContext(artists, {
      resetGenreFilter: true,
      showGenreSidebarFilter: true,
    });
  } catch (error) {
    // Este erro ocorre se a resposta não for JSON válido ou houver falha de rede.
    console.error("Erro ao buscar artistas:", error);
    artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Falha na comunicação com o servidor.</strong> Verifique a consola (F12) para detalhes do erro: ${error.message}</div>`;
  }
}

// Renderizar a lista de artistas na sidebar
function renderArtistsList(artists) {
  artistsList.innerHTML = "";
  currentArtistsById = new Map();

  if (!artists || artists.length === 0) {
    artistsList.innerHTML =
      '<div style="padding:10px; color: var(--text-muted); text-align: center;">Nenhum artista registado neste concelho.</div>';
    return;
  }

  artists.forEach((artist) => {
    const artistId = getArtistNumericId(artist);
    const isOwnArtistAccount = isCurrentSessionOwnArtistId(artistId);
    const upvotes = normalizeArtistUpvotes(artist);
    const upvotedByUser = hasArtistUpvoted(artist);
    const safeArtistName = escapeHtml(artist.name || "artista");

    if (artistId > 0) {
      currentArtistsById.set(artistId, {
        ...artist,
        id: artistId,
        upvotes,
        has_upvoted: upvotedByUser,
      });
    }

    // Cria o elemento principal do cartão
    const card = document.createElement("div");
    card.className = `artist-card${isOwnArtistAccount ? " artist-card-own" : ""}`;
    if (artistId > 0) {
      card.setAttribute("data-artist-card-id", String(artistId));
    }
    card.setAttribute("role", "button");
    card.setAttribute("tabindex", "0");
    card.setAttribute(
      "aria-label",
      `Abrir perfil de ${artist.name || "artista"}`,
    );

    // Define imagem padrão se não existir (usando UI Avatars para ficar bonito)
    const imgUrl =
      artist.image ||
      `https://ui-avatars.com/api/?name=${encodeURIComponent(artist.name)}&background=random&color=fff`;
    const genre = artist.genre || "Geral";
    const locationLabel = [artist.council, artist.district]
      .filter(Boolean)
      .join(" • ");

    // Preenche o HTML do cartão
    card.innerHTML = `
      <img src="${imgUrl}" alt="${artist.name}" class="artist-card-img">
      
      <div class="artist-card-info">
        <button
          type="button"
          class="artist-card-name artist-card-name-btn"
          data-artist-name-button="${artistId}"
          aria-label="Ver conta de ${safeArtistName}"
        >${artist.name}</button>
        <div class="artist-card-genre">${genre}</div>
        <div class="artist-card-location-row">
          <div class="artist-card-location">${locationLabel || "Portugal"}</div>
          <div class="artist-card-upvotes" data-artist-upvotes="${artistId}">▲ ${upvotes}</div>
        </div>
      </div>

      <div class="artist-card-actions">
        ${
          !isOwnArtistAccount
            ? `
        <button
          type="button"
          class="artist-upvote-btn ${upvotedByUser ? "is-active" : ""}"
          data-upvote-button="${artistId}"
          data-artist-id="${artistId}"
          aria-label="Dar upvote a ${safeArtistName}"
          aria-pressed="${upvotedByUser ? "true" : "false"}"
          ${artistId > 0 ? "" : "disabled"}
        >▲ Upvote</button>
        `
            : '<div class="artist-own-pill">A tua conta</div>'
        }
        <div class="artist-play-icon">Ver perfil</div>
      </div>
    `;

    const upvoteButton =
      artistId > 0
        ? card.querySelector(`[data-upvote-button="${artistId}"]`)
        : null;

    if (upvoteButton) {
      upvoteButton.addEventListener("click", async (event) => {
        event.preventDefault();
        event.stopPropagation();
        await handleArtistUpvote(artistId);
      });
    }

    const artistNameButton =
      artistId > 0
        ? card.querySelector(`[data-artist-name-button="${artistId}"]`)
        : null;

    if (artistNameButton) {
      artistNameButton.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (currentArtistsById.has(artistId)) {
          showArtistDetails(currentArtistsById.get(artistId));
          return;
        }

        showArtistDetails(artist);
      });
    }

    card.addEventListener("click", () => {
      if (artistId > 0 && currentArtistsById.has(artistId)) {
        showArtistDetails(currentArtistsById.get(artistId));
        return;
      }

      showArtistDetails(artist);
    });

    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        if (artistId > 0 && currentArtistsById.has(artistId)) {
          showArtistDetails(currentArtistsById.get(artistId));
          return;
        }

        showArtistDetails(artist);
      }
    });

    artistsList.appendChild(card);
  });
}

// Voltar para a vista do concelho
backToCouncilBtn.addEventListener("click", () => {
  closeArtistModal();
  artistDetailsPanel.style.display = "none";
  infoBox.style.display = "block";
});

// ============================================
// CARREGAMENTO DOS LIMITES DE PORTUGAL
// ============================================

const PORTUGAL_BOUNDARY_FALLBACK_URL = "data/municipios_pt.geojson";

function buildMainlandGeometryFromFeatureCollection(featureCollection) {
  if (!featureCollection || !Array.isArray(featureCollection.features)) {
    return null;
  }

  const normalize = (s) =>
    (s || "")
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");

  const polygons = [];

  featureCollection.features.forEach((feature) => {
    if (!feature || !feature.geometry) return;

    const props = feature.properties || {};
    const distrito = normalize(props.distrito_ilha || props.distrito || "");

    // Excluir ilhas para manter apenas continente
    if (distrito.includes("madeira")) return;
    if (distrito.includes("acores")) return;

    if (feature.geometry.type === "Polygon") {
      polygons.push(feature.geometry.coordinates);
    } else if (feature.geometry.type === "MultiPolygon") {
      polygons.push(...feature.geometry.coordinates);
    }
  });

  if (polygons.length === 0) {
    return null;
  }

  return {
    type: "MultiPolygon",
    coordinates: polygons,
  };
}

function applyPortugalBoundary(mainlandGeometry) {
  portugalGeoJSON = mainlandGeometry;

  // Criar camada da fronteira (apenas continente)
  portugalLayer = L.geoJSON(portugalGeoJSON, {
    style: {
      color: "#4cc9f0",
      weight: 2,
      fill: false,
    },
    interactive: false,
  });

  // Criar máscara que escurece todo o mundo exceto o continente
  const outer = [
    [90, -180],
    [90, 180],
    [-90, 180],
    [-90, -180],
  ];

  const holes = [];

  if (portugalGeoJSON.type === "Polygon") {
    const rings = portugalGeoJSON.coordinates;
    holes.push(rings[0].map(([lng, lat]) => [lat, lng]));
  } else if (portugalGeoJSON.type === "MultiPolygon") {
    portugalGeoJSON.coordinates.forEach((poly) => {
      if (Array.isArray(poly) && Array.isArray(poly[0])) {
        holes.push(poly[0].map(([lng, lat]) => [lat, lng]));
      }
    });
  }

  const maskRings = [outer, ...holes];

  outsideMask = L.polygon(maskRings, {
    fillColor: "#333333",
    fillOpacity: 1,
    stroke: false,
    interactive: false,
    className: "outside-mask",
  });

  // Ajustar vista e limites do mapa
  const bounds = portugalLayer.getBounds();
  map.fitBounds(bounds, { padding: [50, 50] });
  map.setMaxBounds(bounds.pad(0.25));

  const minZoom = map.getZoom();
  map.setMinZoom(minZoom + 1);
}

// Buscar limite administrativo de Portugal e aplicar máscara
async function loadPortugalBoundary() {
  showLoading(true);

  try {
    let mainlandGeometry = null;
    let usedFallback = false;

    // 1) Tentar origem externa (Nominatim)
    try {
      const response = await makeNominatimRequest(
        `${NOMINATIM_URL}/search?q=Portugal&format=geojson&polygon_geojson=1&limit=1`,
      );
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();

      if (data.features && data.features.length > 0) {
        const fullGeom = data.features[0].geometry;

        function ringArea(ring) {
          let area = 0;
          for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
            const xi = ring[i][0],
              yi = ring[i][1];
            const xj = ring[j][0],
              yj = ring[j][1];
            area += xj * yi - xi * yj;
          }
          return Math.abs(area / 2);
        }

        if (fullGeom.type === "Polygon") {
          mainlandGeometry = fullGeom;
        } else if (fullGeom.type === "MultiPolygon") {
          let maxArea = -1;
          for (const poly of fullGeom.coordinates) {
            const outerRing = poly[0];
            const area = ringArea(outerRing);
            if (area > maxArea) {
              maxArea = area;
              mainlandGeometry = { type: "Polygon", coordinates: [outerRing] };
            }
          }
        }
      }
    } catch (apiError) {
      console.warn("Falha ao carregar limites via Nominatim:", apiError);
    }

    // 2) Fallback local para reduzir falhas recorrentes
    if (!mainlandGeometry) {
      const fallbackResponse = await fetch(PORTUGAL_BOUNDARY_FALLBACK_URL, {
        cache: "no-store",
      });

      if (!fallbackResponse.ok) {
        throw new Error(
          `Fallback local indisponível (HTTP ${fallbackResponse.status})`,
        );
      }

      const fallbackData = await fallbackResponse.json();
      mainlandGeometry =
        buildMainlandGeometryFromFeatureCollection(fallbackData);
      usedFallback = true;
    }

    if (!mainlandGeometry) {
      throw new Error("Não foi possível construir geometria de Portugal.");
    }

    applyPortugalBoundary(mainlandGeometry);

    showTempMessage(
      usedFallback
        ? "Mapa limitado a Portugal ✓ (dados locais)"
        : "Mapa limitado a Portugal ✓",
      "success",
    );
  } catch (error) {
    console.error("Erro ao carregar limites de Portugal:", error);
    showTempMessage("Erro ao carregar limites de Portugal", "error");
  } finally {
    showLoading(false);
  }
}

// ============================================
// EVENTOS DO MAPA
// ============================================

// Evento de clique no mapa
map.on("click", function (e) {
  // Desabilitado: "se clicar sem a opcao das outlines, não dá nada"
  // A interação principal passa a ser via clique na camada de municípios (quando visível).
});

// ============================================
// EVENTOS DE BUSCA
// ============================================

// Abrir sidebar ao focar na busca (se estiver fechada)
cityInput.addEventListener("focus", function () {
  if (getEffectiveSearchMode() === "genre") {
    renderGenreChecklist(this.value.trim());
    return;
  }

  resultsContainer.style.display = "none";
});

cityInput.addEventListener("input", function () {
  clearTimeout(searchTimeout);
  const searchValue = this.value.trim();

  if (clearSearchBtn) {
    clearSearchBtn.classList.toggle("visible", searchValue.length > 0);
  }

  if (getEffectiveSearchMode() === "genre") {
    searchTimeout = setTimeout(() => {
      searchGenres(searchValue);
    }, 140);
    return;
  }

  if (searchValue.length >= 2) {
    searchTimeout = setTimeout(() => {
      searchCities(searchValue);
    }, 280);
  } else {
    activeSearchResultIndex = -1;
    resultsContainer.style.display = "none";
    if (searchHelper) {
      searchHelper.textContent = getMinSearchMessage();
    }
  }
});

if (clearSearchBtn) {
  clearSearchBtn.addEventListener("click", function () {
    clearSearchUI();
    cityInput.focus();
  });
}

// Fechar resultados ao clicar fora
// (Removido pois agora estão dentro da sidebar, o comportamento é diferente)
// document.addEventListener("click", function (e) {
//   if (!cityInput.contains(e.target) && !resultsContainer.contains(e.target)) {
//     resultsContainer.style.display = "none";
//   }
// });

// ============================================
// EVENTOS DOS BOTÕES
// ============================================

// Toggle do Menu/Sidebar
menuToggle.addEventListener("click", function () {
  sidebar.classList.toggle("open");
  if (sidebar.classList.contains("open")) {
    setTimeout(() => cityInput.focus(), 100);
  }
});

// Fechar caixa de informações
closeBtn.addEventListener("click", function () {
  clearSelectedRegion();
  infoBox.style.display = "none";
  infoBoxVisible = false;
  artistDetailsPanel.style.display = "none";
  sidebar.classList.remove("open");
});

// Localizar usuário
locateBtn.addEventListener("click", function () {
  const isLocalhost = ["localhost", "127.0.0.1", "::1"].includes(
    window.location.hostname,
  );
  const isSecureOrigin =
    window.isSecureContext ||
    window.location.protocol === "https:" ||
    isLocalhost;

  if (!isSecureOrigin) {
    showTempMessage(
      "Geolocalização requer HTTPS. Em desenvolvimento, use localhost.",
      "error",
    );
    return;
  }

  if (!navigator.geolocation) {
    showTempMessage("Geolocalização não suportada pelo navegador", "error");
    return;
  }

  showLoading(true);

  navigator.geolocation.getCurrentPosition(
    async function (position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;

      try {
        const response = await makeNominatimRequest(
          `${NOMINATIM_URL}/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`,
        );

        const data = await response.json();

        // Verificar se está em Portugal
        if (portugalGeoJSON && !isPointInGeoJSON(portugalGeoJSON, lat, lng)) {
          showTempMessage(
            "A sua localização está fora de Portugal — a interação está limitada a Portugal",
            "warning",
          );
          if (portugalLayer) {
            map.fitBounds(portugalLayer.getBounds(), { padding: [50, 50] });
          }
          showLoading(false);
          return;
        }

        if (data && data.address) {
          // Tentar selecionar o município correspondente na camada
          let municipioEncontrado = false;
          const nomeMunicipio =
            data.address.municipality ||
            data.address.city ||
            data.address.town ||
            "";

          if (municipiosLayer && nomeMunicipio) {
            const normalize = (s) =>
              (s || "")
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");
            const busca = normalize(nomeMunicipio);

            municipiosLayer.eachLayer(function (layer) {
              if (municipioEncontrado) return;
              const nomeLayer = normalize(layer.feature.properties.municipio);
              if (nomeLayer === busca) {
                selecionarMunicipio(layer.feature, layer);
                municipioEncontrado = true;
                showTempMessage(`${nomeMunicipio} selecionado`, "success");
              }
            });
          }

          if (!municipioEncontrado) {
            // Se não encontrar por nome, procurar pelo ponto (lat/lng) dentro de algum município
            if (municipiosLayer) {
              municipiosLayer.eachLayer(function (layer) {
                if (municipioEncontrado) return;
                if (
                  layer.feature.geometry.type === "Polygon" ||
                  layer.feature.geometry.type === "MultiPolygon"
                ) {
                  if (isPointInPolygon([lng, lat], layer.feature.geometry)) {
                    selecionarMunicipio(layer.feature, layer);
                    municipioEncontrado = true;
                    showTempMessage(
                      `${layer.feature.properties.municipio} selecionado`,
                      "success",
                    );
                  }
                }
              });
            }
          }

          if (!municipioEncontrado) {
            // Fallback se não encontrar município
            showTempMessage(
              "Não foi possível identificar o conselho - tente clicar no mapa",
              "warning",
            );
            map.setView([lat, lng], 14);
          }
        } else {
          // Tratamento quando não encontra dados
          showTempMessage(
            "Nenhuma informação encontrada para sua localização",
            "warning",
          );
          map.setView([lat, lng], 14);
        }
        showLoading(false);
      } catch (error) {
        showLoading(false);
        console.error("Erro ao buscar cidade:", error);
        showTempMessage("Erro ao buscar cidade próxima", "error");
      }
    },
    function (error) {
      showLoading(false);
      if (
        error &&
        typeof error.message === "string" &&
        /secure origins|only secure/i.test(error.message)
      ) {
        showTempMessage(
          "Geolocalização bloqueada: abra o site em HTTPS ou em localhost.",
          "error",
        );
        return;
      }
      showTempMessage(
        "Não foi possível obter sua localização: " + error.message,
        "error",
      );
    },
  );
});

// Botão de Município Aleatório
if (randomBtn) {
  randomBtn.addEventListener("click", async function () {
    if (randomSelectionInProgress) {
      return;
    }

    randomSelectionInProgress = true;
    if (randomBtn) {
      randomBtn.disabled = true;
    }

    try {
      const isDistritosMode = getEffectiveLayerType() === "distritos";

      if (isDistritosMode) {
        await loadDistritos();
      } else {
        await loadAndShowMunicipios();
      }

      const activeLayerGroup = isDistritosMode
        ? distritosLayer
        : municipiosLayer;
      const selectedLayer = isDistritosMode
        ? distritoSelecionado
        : municipioSelecionado;

      if (!activeLayerGroup || activeLayerGroup.getLayers().length === 0) {
        showTempMessage(
          isDistritosMode
            ? "A aguardar carregamento dos distritos..."
            : "A aguardar carregamento dos concelhos...",
          "warning",
        );
        return;
      }

      await ensureArtistCoverageLoaded();

      const layers = activeLayerGroup.getLayers();
      const filteredLayers = layers.filter((layer) => {
        if (!layer?.feature) return false;
        if (selectedLayer && layer === selectedLayer) return false;
        if (layer._selected) return false;

        if (isDistritosMode) {
          const distrito = normalizeDistrictName(
            getFeatureDistritoName(layer.feature),
          );
          return distrito && artistDistrictsSet?.has(distrito);
        }

        const municipio = normalizeText(getFeatureMunicipioName(layer.feature));
        return municipio && artistCouncilsSet?.has(municipio);
      });

      if (filteredLayers.length === 0) {
        showTempMessage(
          isDistritosMode
            ? "Não existem distritos com artistas disponíveis."
            : "Não existem concelhos com artistas disponíveis.",
          "warning",
        );
        return;
      }

      const randomIndex = Math.floor(Math.random() * filteredLayers.length);
      const randomLayer = filteredLayers[randomIndex];

      if (isDistritosMode) {
        selecionarDistrito(randomLayer.feature, randomLayer);
        showTempMessage(
          `🎲 Sugestão: ${getFeatureDistritoName(randomLayer.feature)}`,
          "success",
        );
        return;
      }

      selecionarMunicipio(randomLayer.feature, randomLayer);
      showTempMessage(
        `🎲 Sugestão: ${getFeatureMunicipioName(randomLayer.feature)}`,
        "success",
      );
    } catch (error) {
      console.error("Erro ao processar sugestão aleatória:", error);
      showTempMessage("Não foi possível gerar sugestão aleatória.", "error");
    } finally {
      randomSelectionInProgress = false;
      if (randomBtn) {
        randomBtn.disabled = false;
      }
    }
  });
}

// Resetar vista do mapa
resetBtn.addEventListener("click", function () {
  if (portugalLayer) {
    map.fitBounds(portugalLayer.getBounds(), { padding: [50, 50] });
  } else {
    map.setView([20, 0], 2);
  }

  if (currentMarker) {
    map.removeLayer(currentMarker);
    currentMarker = null;
  }

  if (currentCityBoundary) {
    map.removeLayer(currentCityBoundary);
    currentCityBoundary = null;
  }

  // Remover destaque de município
  if (municipiosLayer) {
    municipiosLayer.eachLayer(function (l) {
      if (l._selected) {
        municipiosLayer.resetStyle(l);
        l._selected = false;
      }
    });
    municipioSelecionado = null;
  }

  infoBox.style.display = "none";
  infoBoxVisible = false;
  artistDetailsPanel.style.display = "none";
});

// Função para carregar e mostrar a camada de municípios ao iniciar
async function loadAndShowMunicipios() {
  if (municipiosLayer) {
    return municipiosLayer;
  }

  if (municipiosLoadPromise) {
    return municipiosLoadPromise;
  }

  municipiosLoadPromise = (async () => {
    cityInput.disabled = true;
    cityInput.placeholder = "A carregar concelhos...";
    showTempMessage("A carregar limites dos concelhos...", "info", 6000);

    municipiosLayer = await carregarMunicipiosPortugueses();

    if (municipiosLayer) {
      map.addLayer(municipiosLayer);
      updateLegend();
      setLegendVisibility(true);
      showTempMessage("Limites dos concelhos carregados ✓", "success");
    } else {
      showTempMessage("Não foi possível carregar os concelhos.", "error");
    }

    cityInput.disabled = false;
    cityInput.placeholder = "Pesquisar cidade ou concelho...";

    return municipiosLayer;
  })().finally(() => {
    municipiosLoadPromise = null;
  });

  return municipiosLoadPromise;
}

// Mostrar/ocultar legenda
legendToggle.addEventListener("click", function () {
  const isVisible = municipiosLegend.classList.contains("show");
  const nextVisible = !isVisible;

  setLegendVisibility(nextVisible);
  showTempMessage(nextVisible ? "Legenda visível" : "Legenda oculta", "info");
});

// ============================================
// CIDADES INICIAIS
// ============================================

// Adicionar cidades iniciais
const initialCities = [
  {
    name: "Lisboa",
    lat: 38.7223,
    lng: -9.1393,
    countryName: "Portugal",
    adminName1: "Lisboa",
    population: 504718,
    type: "city",
    osm_id: 5403416,
    osm_type: "relation",
  },
  {
    name: "Porto",
    lat: 41.1579,
    lng: -8.6291,
    countryName: "Portugal",
    adminName1: "Porto",
    population: 214349,
    type: "city",
    osm_id: 5403413,
    osm_type: "relation",
  },
  {
    name: "Braga",
    lat: 41.5503,
    lng: -8.4201,
    countryName: "Portugal",
    adminName1: "Braga",
    type: "city",
    osm_id: 5403414,
    osm_type: "relation",
  },
  {
    name: "Coimbra",
    lat: 40.2033,
    lng: -8.4103,
    countryName: "Portugal",
    adminName1: "Coimbra",
    type: "city",
    osm_id: 5403415,
    osm_type: "relation",
  },
];

// Adicionar marcadores iniciais
function addInitialCities() {
  initialCities.forEach((city) => {
    const marker = L.marker([city.lat, city.lng], { opacity: 0 })
      .addTo(map)
      .bindPopup(
        `<b>${city.name}</b><br>${city.countryName}<br><small>Clique para selecionar</small>`,
      );

    // Se tivermos o polígono de Portugal, verificar se a cidade está dentro
    if (
      portugalGeoJSON &&
      !isPointInGeoJSON(portugalGeoJSON, city.lat, city.lng)
    ) {
      marker.setOpacity(0.45);
      marker.off("click");
    } else {
      marker.on("click", function () {
        selectCity(city);
      });
    }

    marker.on("mouseover", function () {
      this.openPopup();
    });

    marker.on("mouseout", function () {
      this.closePopup();
    });
  });
}

// ============================================
// EVENTOS DE TECLADO
// ============================================

document.addEventListener("keydown", function (e) {
  if (onboardingActive && e.key === "Escape") {
    return;
  }

  if (e.key === "Escape") {
    resultsContainer.style.display = "none";
    infoBox.style.display = "none";
    infoBoxVisible = false;
  }
});

cityInput.addEventListener("keydown", function (e) {
  if (getEffectiveSearchMode() === "genre") {
    if (e.key === "Escape") {
      e.preventDefault();
      resultsContainer.style.display = "none";
      if (searchHelper) {
        searchHelper.textContent =
          "Pesquisa de géneros fechada. Clica novamente para reabrir.";
      }
      return;
    }

    if (e.key === "Enter") {
      e.preventDefault();
      runGenreSelectionSearch();
    }

    return;
  }

  const items = getVisibleSearchItems();

  if (e.key === "ArrowDown" && items.length > 0) {
    e.preventDefault();
    const nextIndex =
      activeSearchResultIndex < 0
        ? 0
        : Math.min(activeSearchResultIndex + 1, items.length - 1);
    setActiveSearchResult(nextIndex);
    return;
  }

  if (e.key === "ArrowUp" && items.length > 0) {
    e.preventDefault();
    const prevIndex =
      activeSearchResultIndex < 0
        ? items.length - 1
        : Math.max(activeSearchResultIndex - 1, 0);
    setActiveSearchResult(prevIndex);
    return;
  }

  if (e.key === "Escape") {
    e.preventDefault();
    resultsContainer.style.display = "none";
    activeSearchResultIndex = -1;
    if (searchHelper) {
      searchHelper.textContent =
        "Pesquisa fechada. Continua a escrever para procurar.";
    }
    return;
  }

  if (e.key === "Enter") {
    e.preventDefault();

    if (items.length > 0) {
      const selectedIndex =
        activeSearchResultIndex >= 0 ? activeSearchResultIndex : 0;
      items[selectedIndex].click();
      return;
    }

    if (this.value.trim().length >= 2) {
      searchCities(this.value);
    }
  }
});

function setupLayerSwitch() {
  if (!layerToggle) return;

  layerToggle.checked = false;
  if (layerSwitcher) {
    layerSwitcher.dataset.layer = "municipios";
  }
  updateSearchContextUI();

  layerToggle.addEventListener("change", () => {
    switchLayer(layerToggle.checked ? "distritos" : "municipios");
  });
}

function setupSearchModeSwitch() {
  if (!searchModeToggle) {
    return;
  }

  searchModeToggle.checked = false;
  if (searchModeSwitcher) {
    searchModeSwitcher.dataset.mode = "location";
  }

  updateSearchContextUI();

  searchModeToggle.addEventListener("change", () => {
    resultsContainer.style.display = "none";
    activeSearchResultIndex = -1;

    if (getEffectiveSearchMode() === "location") {
      selectedGenres = new Set();
    }

    updateSearchContextUI();

    const query = cityInput.value.trim();
    if (getEffectiveSearchMode() === "genre") {
      searchGenres(query);
      return;
    }

    if (query.length >= 2) {
      searchCities(query);
    }
  });
}

async function getDistritosData() {
  if (distritosDataCache) {
    return distritosDataCache;
  }

  if (distritosLoadPromise) {
    return distritosLoadPromise;
  }

  distritosLoadPromise = (async () => {
    const response = await fetch(DISTRICTS_GEOJSON_URL, {
      cache: "force-cache",
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    const normalizedData = normalizeDistrictGeoJSON(data);
    distritosDataCache = normalizedData;
    return normalizedData;
  })();

  try {
    return await distritosLoadPromise;
  } finally {
    distritosLoadPromise = null;
  }
}

function preloadDistritosInBackground() {
  if (distritosDataCache || distritosLoadPromise) {
    return;
  }

  setTimeout(async () => {
    try {
      await getDistritosData();
      console.log("✅ Distritos pré-carregados em background");
    } catch (error) {
      console.warn("Pré-carregamento de distritos falhou:", error);
    }
  }, 600);
}

function switchLayer(type) {
  if (currentLayerType === type) return; // Já está ativo

  if (layerToggle) {
    layerToggle.checked = type === "distritos";
  }

  if (layerSwitcher) {
    layerSwitcher.dataset.layer = type;
  }

  if (type === "municipios") {
    // Mostrar Municípios, Esconder Distritos
    if (distritosLayer) map.removeLayer(distritosLayer);
    if (municipiosLayer) map.addLayer(municipiosLayer);
    showLoading(false);

    // Atualizar legenda se necessário
    document.querySelector("#municipiosLegend h4").textContent = "Concelhos";
    showTempMessage("A visualizar Concelhos", "info");
  } else {
    // Esconder Municípios
    if (municipiosLayer) map.removeLayer(municipiosLayer);

    // Carregar ou Mostrar Distritos
    if (!distritosLayer) {
      loadDistritos();
    } else {
      map.addLayer(distritosLayer);
      showTempMessage("A visualizar Distritos", "info");
    }

    // Atualizar legenda
    document.querySelector("#municipiosLegend h4").textContent = "Distritos";
  }

  currentLayerType = type;
  updateSearchContextUI();
  updateLabelsVisibility();
}

async function loadDistritos() {
  if (distritosLayer) {
    if (currentLayerType === "distritos" && !map.hasLayer(distritosLayer)) {
      map.addLayer(distritosLayer);
      updateLabelsVisibility();
    }
    return;
  }

  showLoading(true);

  try {
    const normalizedData = await getDistritosData();

    const createdLayer = L.geoJSON(normalizedData, {
      style: function (feature) {
        const nome =
          feature.properties.distrito ||
          feature.properties.name ||
          "Desconhecido";
        const cor = getDistrictColorByName(nome, "#3388ff");
        return {
          fillColor: cor,
          fillOpacity: 0.2,
          color: cor,
          weight: 2,
          opacity: 1,
          className: "distrito-layer",
        };
      },
      onEachFeature: function (feature, layer) {
        const nome = feature.properties.distrito || feature.properties.name;

        // Tooltip com nome do distrito
        layer.bindTooltip(nome, {
          permanent: true,
          direction: "center",
          className: "municipio-label", // Reutiliza estilo
        });

        // Posicionar o tooltip no centróide real do polígono do distrito
        try {
          const centroid = computeFeatureCentroid(feature.geometry);
          const tooltip = layer.getTooltip();
          if (centroid && tooltip) {
            tooltip.setLatLng(centroid);
          }
        } catch (err) {
          console.warn("Não foi possível calcular centróide do distrito:", err);
        }

        // Inicialmente ocultar o rótulo se o zoom for muito baixo
        if (map.getZoom() < MIN_ZOOM_FOR_LABELS) {
          layer.closeTooltip();
        }

        layer.on({
          mouseover: function (e) {
            if (!layer._selected) {
              layer.setStyle({
                fillOpacity: 0.5,
                weight: 3,
              });
            }
            if (map.getZoom() < MIN_ZOOM_FOR_LABELS) {
              layer.openTooltip();
            }
          },
          mouseout: function (e) {
            if (!layer._selected) {
              distritosLayer.resetStyle(layer);
            }
            if (map.getZoom() < MIN_ZOOM_FOR_LABELS) {
              layer.closeTooltip();
            }
          },
          click: function (e) {
            selecionarDistrito(feature, layer);
            e.originalEvent.preventDefault();
            e.originalEvent.stopPropagation();
          },
        });
      },
    });

    distritosLayer = createdLayer;

    if (currentLayerType === "distritos") {
      distritosLayer.addTo(map);
    }

    // Garante visibilidade correta dos rótulos logo no 1º carregamento
    updateLabelsVisibility();

    showTempMessage("Distritos carregados com sucesso", "success");
  } catch (err) {
    console.error("Erro ao carregar distritos:", err);
    showTempMessage("Erro ao carregar distritos", "error");
  } finally {
    showLoading(false);
  }
}

function normalizeDistrictGeoJSON(geojson) {
  if (
    !geojson ||
    !Array.isArray(geojson.features) ||
    geojson.features.length === 0
  ) {
    return geojson;
  }

  const firstFeature = geojson.features[0];
  const firstCoordinate = getFirstCoordinate(
    firstFeature?.geometry?.coordinates,
  );

  if (!firstCoordinate || firstCoordinate.length < 2) {
    return geojson;
  }

  const [x, y] = firstCoordinate;
  const appearsProjected = Math.abs(x) > 180 || Math.abs(y) > 90;

  if (!appearsProjected) {
    return geojson;
  }

  if (typeof proj4 === "undefined") {
    throw new Error(
      "Proj4 não está disponível para converter o GeoJSON de distritos.",
    );
  }

  proj4.defs(
    "EPSG:3763",
    "+proj=tmerc +lat_0=39.66825833333333 +lon_0=-8.133108333333333 +k=1 +x_0=0 +y_0=0 +ellps=GRS80 +units=m +no_defs",
  );

  return {
    ...geojson,
    crs: {
      type: "name",
      properties: { name: "urn:ogc:def:crs:OGC:1.3:CRS84" },
    },
    features: geojson.features.map((feature) => ({
      ...feature,
      geometry: {
        ...feature.geometry,
        coordinates: transformCoordinatesToWGS84(feature.geometry.coordinates),
      },
    })),
  };
}

function getFirstCoordinate(coordinates) {
  if (!Array.isArray(coordinates) || coordinates.length === 0) {
    return null;
  }

  if (typeof coordinates[0] === "number") {
    return coordinates;
  }

  return getFirstCoordinate(coordinates[0]);
}

function transformCoordinatesToWGS84(coordinates) {
  if (!Array.isArray(coordinates)) {
    return coordinates;
  }

  if (
    typeof coordinates[0] === "number" &&
    typeof coordinates[1] === "number"
  ) {
    return proj4("EPSG:3763", "EPSG:4326", coordinates);
  }

  return coordinates.map(transformCoordinatesToWGS84);
}

function selecionarDistrito(feature, layer) {
  const nome =
    feature?.properties?.distrito || feature?.properties?.name || "Distrito";

  // Se o distrito clicado já está selecionado, desmarca-o e fecha menu
  if (layer._selected) {
    layer._selected = false;
    if (distritosLayer) {
      distritosLayer.resetStyle(layer);
    }
    distritoSelecionado = null;
    infoBox.style.display = "none";
    infoBoxVisible = false;
    artistDetailsPanel.style.display = "none";
    sidebar.classList.remove("open");
    return;
  }

  // Se existir município selecionado, desmarca-o
  if (municipioSelecionado && municipiosLayer) {
    municipioSelecionado._selected = false;
    municipiosLayer.resetStyle(municipioSelecionado);
    municipioSelecionado = null;
  }

  // Se outro distrito estava selecionado, desmarca-o
  if (distritoSelecionado && distritosLayer) {
    distritoSelecionado._selected = false;
    distritosLayer.resetStyle(distritoSelecionado);
  }

  layer._selected = true;
  layer.bringToFront();
  layer.setStyle({
    fillOpacity: 0.5,
    weight: 3,
    opacity: 1,
    className: "distrito-layer municipio-highlight",
  });
  distritoSelecionado = layer;

  animateToLayerBounds(layer.getBounds(), {
    padding: [50, 50],
    maxZoom: 10,
  });
  showDistrictInfo(nome);
}

// Função simples para mostrar info do distrito (opcional)
function showDistrictInfo(nome) {
  closeArtistModal();

  if (!sidebar.classList.contains("open")) {
    sidebar.classList.add("open");
  }

  const districtName = nome || "Distrito";
  cityName.textContent = districtName;
  cityDetails.innerHTML = "<p>Modo de visualização de Distritos</p>";
  cityImageContainer.style.display = "none";
  cityDetails.style.display = "none";
  document.querySelector(".coordinates").style.display = "none";

  // Esconder painel de detalhes do artista (se aberto) e mostrar a secção de artistas
  artistDetailsPanel.style.display = "none";
  councilArtistsSection.style.display = "block";
  artistsList.innerHTML =
    '<div style="padding:10px; color: var(--text-muted);">A carregar artistas do distrito...</div>';

  // Limpar estado de pesquisa e mostrar a infoBox para exibir os artistas
  resultsContainer.style.display = "none";
  infoBox.style.display = "block";
  infoBoxVisible = true;

  // Carregar artistas para o distrito após aplicar o estado visual
  requestAnimationFrame(() => {
    loadArtistsForDistrict(districtName);
  });
}

// Carregar artistas por distrito
async function loadArtistsForDistrict(districtName) {
  if (!districtName) return;

  councilArtistsSection.style.display = "block";
  if (artistsFilters) {
    artistsFilters.style.display = "none";
  }
  artistsList.innerHTML =
    '<div style="padding:10px; color: var(--text-muted);">A carregar artistas...</div>';

  try {
    const response = await fetch(
      `api/get_artists.php?district=${encodeURIComponent(districtName)}`,
    );

    if (!response.ok) {
      const errorText = await response.text();
      console.error(
        "Erro na API (district):",
        response.status,
        response.statusText,
        errorText,
      );
      artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Erro de Rede ${response.status}:</strong> ${response.statusText}.</div>`;
      return;
    }

    const data = await response.json();

    if (data.error) {
      console.error("Erro da Base de Dados (district):", data.error);
      artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Erro no Servidor:</strong> ${data.error}</div>`;
      return;
    }

    const artists = Array.isArray(data) ? data : [];
    setArtistsForCurrentContext(artists, {
      resetGenreFilter: true,
      showGenreSidebarFilter: true,
    });
  } catch (error) {
    console.error("Erro ao buscar artistas (district):", error);
    artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Falha na comunicação com o servidor.</strong> ${error.message}</div>`;
  }
}

function showGenresInfo(genres) {
  closeArtistModal();

  if (!sidebar.classList.contains("open")) {
    sidebar.classList.add("open");
  }

  const normalizedGenres = (Array.isArray(genres) ? genres : [])
    .map((genre) => (genre || "").toString().trim())
    .filter(Boolean);

  const titleLabel =
    normalizedGenres.length === 1
      ? `Género: ${normalizedGenres[0]}`
      : `Géneros (${normalizedGenres.length})`;

  cityName.textContent = titleLabel;
  cityDetails.innerHTML = "<p>Artistas encontrados em todo o Portugal.</p>";
  cityImageContainer.style.display = "none";
  cityDetails.style.display = "block";
  document.querySelector(".coordinates").style.display = "none";

  artistDetailsPanel.style.display = "none";
  councilArtistsSection.style.display = "block";
  if (artistsFilters) {
    artistsFilters.style.display = "grid";
  }
  if (artistGenreFilterGroup) {
    artistGenreFilterGroup.style.display = "none";
  }
  artistsList.innerHTML =
    '<div style="padding:10px; color: var(--text-muted);">A carregar artistas por género...</div>';

  if (getEffectiveSearchMode() === "genre") {
    resultsContainer.style.display = "block";
  } else {
    resultsContainer.style.display = "none";
  }
  infoBox.style.display = "block";
  infoBoxVisible = true;

  requestAnimationFrame(() => {
    loadArtistsForGenres(normalizedGenres);
  });
}

function showGenreInfo(genreName) {
  const singleGenre = (genreName || "").toString().trim();
  if (!singleGenre) {
    return;
  }

  showGenresInfo([singleGenre]);
}

async function loadArtistsForGenres(genres) {
  const selected = (Array.isArray(genres) ? genres : [])
    .map((genre) => (genre || "").toString().trim())
    .filter(Boolean);

  if (selected.length === 0) {
    return;
  }

  councilArtistsSection.style.display = "block";
  if (artistsFilters) {
    artistsFilters.style.display = "grid";
  }
  if (artistGenreFilterGroup) {
    artistGenreFilterGroup.style.display = "none";
  }
  artistsList.innerHTML =
    '<div style="padding:10px; color: var(--text-muted);">A carregar artistas...</div>';

  try {
    const response = await fetch(
      `api/get_artists.php?genres=${encodeURIComponent(selected.join(","))}`,
    );

    if (!response.ok) {
      const errorText = await response.text();
      console.error(
        "Erro na API (genre):",
        response.status,
        response.statusText,
        errorText,
      );
      artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Erro de Rede ${response.status}:</strong> ${response.statusText}.</div>`;
      return;
    }

    const data = await response.json();

    if (data.error) {
      console.error("Erro da Base de Dados (genre):", data.error);
      artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Erro no Servidor:</strong> ${data.error}</div>`;
      return;
    }

    const artists = Array.isArray(data) ? data : [];
    setArtistsForCurrentContext(artists, {
      resetGenreFilter: true,
      showGenreSidebarFilter: false,
    });
  } catch (error) {
    console.error("Erro ao buscar artistas (genres):", error);
    artistsList.innerHTML = `<div style="padding:10px; color: #ff4444;"><strong>Falha na comunicação com o servidor.</strong> ${error.message}</div>`;
  }
}

async function loadArtistsForGenre(genreName) {
  const singleGenre = (genreName || "").toString().trim();
  if (!singleGenre) return;
  return loadArtistsForGenres([singleGenre]);
}

// ============================================
// CONTROLE DE VISIBILIDADE DE RÓTULOS (ZOOM)
// ============================================

// Função para atualizar visibilidade dos rótulos baseado no zoom
function updateLabelsVisibility() {
  const currentZoom = map.getZoom();
  const showLabels = currentZoom >= MIN_ZOOM_FOR_LABELS;

  // Atualizar visibilidade para municípios (só quando a camada ativa for municípios)
  if (municipiosLayer) {
    municipiosLayer.eachLayer(function (layer) {
      if (currentLayerType === "municipios" && showLabels) {
        layer.openTooltip();
      } else {
        layer.closeTooltip();
      }
    });
  }

  // Atualizar visibilidade para distritos (só quando a camada ativa for distritos)
  if (distritosLayer) {
    distritosLayer.eachLayer(function (layer) {
      if (currentLayerType === "distritos" && showLabels) {
        layer.openTooltip();
      } else {
        layer.closeTooltip();
      }
    });
  }
}

// ============================================
// CONTROLE DE TEMA
// ============================================

// Inicializar tema a partir do localStorage
function initTheme() {
  applyTheme();
}

// Aplicar tema
function applyTheme() {
  const body = document.body;

  body.classList.remove("light-theme");
  localStorage.setItem("theme", "dark");

  // Atualizar cores do mapa quando o tema muda
  updateMapColors();
  updateLegend();
}

// Função para atualizar as cores do mapa após mudança de tema
function updateMapColors() {
  if (municipiosLayer) {
    municipiosLayer.setStyle(function (feature) {
      const distrito = feature.properties.distrito_ilha || "Desconhecido";
      const cor = getDistrictColorByName(distrito, "#757575");

      return {
        fillColor: cor,
        fillOpacity: 0.15,
        color: cor,
        weight: 1,
        opacity: 0.8,
      };
    });
  }

  if (distritosLayer) {
    distritosLayer.setStyle(function (feature) {
      const nome =
        feature.properties.distrito ||
        feature.properties.name ||
        "Desconhecido";
      const cor = getDistrictColorByName(nome, "#3388ff");

      return {
        fillColor: cor,
        fillOpacity: 0.2,
        color: cor,
        weight: 2,
        opacity: 1,
      };
    });
  }
}

// ============================================
// AUTENTICAÇÃO DE UTILIZADOR
// ============================================

async function checkUserSession() {
  try {
    const response = await fetch("api/check_session.php", {
      cache: "no-store",
    });
    const data = await response.json();

    if (data.loggedIn) {
      const isArtist = data.userType === "artist";
      currentSessionUserType = data.userType || null;
      currentSessionAccountId = Number(data.accountId || 0);
      currentSessionUsername = (data.username || "").toString();
      currentSessionEmail = (data.email || "").toString().trim();
      if (typeof window.BeatmapApplyContactSessionEmail === "function") {
        window.BeatmapApplyContactSessionEmail(currentSessionEmail);
      }
      currentSessionArtistIsPending =
        isArtist && data.artistModerationStatus === "pending";
      const artistBadge = isArtist
        ? '<span class="user-type-badge artist-badge">artist</span>'
        : "";
      const pendingBadge =
        isArtist && data.artistModerationStatus === "pending"
          ? '<span class="user-type-badge pending-badge">pendente</span>'
          : "";

      // Utilizador está logado
      // Atualizar o botão principal para mostrar nome + seta
      const accountBtn = document.getElementById("accountBtn");
      accountBtn.innerHTML = `
        <span>${data.username}</span>
        ${artistBadge}
        ${pendingBadge}
        <span style="font-size: 10px; margin-left: 4px;">▼</span>
      `;
      accountBtn.title = "Minha Conta";

      // Determinar o link do perfil com base no tipo de utilizador
      const profileUrl =
        data.userType === "artist"
          ? "/beatmap/views/edit_artist.php"
          : "../beatmap(index)/perfil.php";
      const showMeOnMapAction = isArtist
        ? `
        <button class="dropdown-action-btn" id="showMeOnMapBtn" title="Ver-me no mapa">
          <span>🗺️</span> Ver-me no mapa
        </button>`
        : "";

      // Preencher o dropdown com as opções pedidas
      accountDropdown.innerHTML = `
        ${showMeOnMapAction}
        <a href="${profileUrl}" class="dropdown-action-btn" title="Editar Perfil">
          <span>✏️</span> Editar Perfil
        </a>
        <div class="dropdown-divider"></div>
        <button class="dropdown-action-btn logout" id="logoutBtnDynamic">
          <span>⏻</span> Sair
        </button>
      `;

      // Adicionar evento ao botão de logout dinâmico
      document
        .getElementById("logoutBtnDynamic")
        .addEventListener("click", async () => {
          await fetch("api/logout.php");
          window.location.reload();
        });

      const showMeOnMapBtn = document.getElementById("showMeOnMapBtn");
      if (showMeOnMapBtn) {
        showMeOnMapBtn.addEventListener("click", async () => {
          accountDropdown.classList.remove("show");
          await focusCurrentArtistOnMap();
        });
      }

      syncGlobalChatAvailability();
      syncPrivateInboxAvailability();

      return true;
    } else {
      currentSessionUserType = null;
      currentSessionAccountId = 0;
      currentSessionUsername = "";
      currentSessionEmail = "";
      currentSessionArtistIsPending = false;
      syncGlobalChatAvailability();
      syncPrivateInboxAvailability();
      window.location.href = "../beatmap(index)/login.php";
      return false;
    }
  } catch (error) {
    console.error("Erro ao verificar sessão:", error);
    currentSessionUserType = null;
    currentSessionAccountId = 0;
    currentSessionUsername = "";
    currentSessionEmail = "";
    currentSessionArtistIsPending = false;
    syncGlobalChatAvailability();
    syncPrivateInboxAvailability();
    window.location.href = "../beatmap(index)/login.php";
    return false;
  }
}

// Eventos para controlar o menu da conta
accountBtn.addEventListener("click", (e) => {
  e.stopPropagation(); // Impede que o clique no botão feche o menu imediatamente
  accountDropdown.classList.toggle("show");
});

privateInboxBtn?.addEventListener("click", async (event) => {
  event.stopPropagation();

  if (currentSessionUserType !== "artist") {
    return;
  }

  const shouldOpen = !privateInboxDropdown?.classList.contains("show");
  if (!privateInboxDropdown || !privateInboxBtn) {
    return;
  }

  if (shouldOpen) {
    privateInboxDropdown.classList.add("show");
    privateInboxBtn.setAttribute("aria-expanded", "true");
    resetPrivateInboxView();
    await fetchPrivateInboxConversations({ silent: false });
    startPrivateInboxPolling();
    return;
  }

  closePrivateInboxDropdown();
});

privateInboxDropdown?.addEventListener("click", (event) => {
  event.stopPropagation();
});

privateInboxList?.addEventListener("click", async (event) => {
  event.stopPropagation();

  const target = event.target instanceof Element ? event.target : null;
  const emojiItemBtn = target?.closest("[data-chat-emoji]");
  if (emojiItemBtn instanceof HTMLElement) {
    const emoji = (emojiItemBtn.getAttribute("data-chat-emoji") || "").trim();
    if (emoji) {
      const form = emojiItemBtn.closest("form[data-private-conversation-form]");
      const textarea = form?.querySelector("[data-private-conversation-input]");
      insertEmojiAtTextareaCursor(textarea, emoji);
    }

    return;
  }

  const emojiToggleBtn = target?.closest("[data-chat-emoji-toggle]");
  if (emojiToggleBtn instanceof HTMLElement) {
    toggleEmojiPickerByButton(emojiToggleBtn);
    return;
  }

  const audioTriggerBtn = target?.closest("[data-private-audio-trigger]");
  if (audioTriggerBtn instanceof HTMLButtonElement) {
    const form = audioTriggerBtn.closest("form[data-private-conversation-form]");
    const fileInput = form?.querySelector("[data-private-audio-input]");
    if (fileInput instanceof HTMLInputElement) {
      fileInput.click();
    }
    return;
  }

  const audioRemoveBtn = target?.closest("[data-private-audio-remove]");
  if (audioRemoveBtn instanceof HTMLButtonElement) {
    clearPrivateConversationPendingAudio(currentPrivateConversationArtistId);
    refreshPrivateConversationComposerUi();
    return;
  }

  const audioRecordBtn = target?.closest("[data-private-audio-record]");
  if (audioRecordBtn instanceof HTMLButtonElement) {
    if (isRecordingPrivateConversationAudioForArtist(currentPrivateConversationArtistId)) {
      await stopPrivateConversationVoiceRecording({ persist: true });
    } else {
      await startPrivateConversationVoiceRecording();
    }
    return;
  }

  const backBtn = target?.closest("[data-private-inbox-back]");
  if (backBtn) {
    resetPrivateInboxView();
    await fetchPrivateInboxConversations({ silent: false });
    return;
  }

  const profileBtn = target?.closest("[data-private-conversation-profile]");
  if (profileBtn) {
    const artistId = Number(
      profileBtn.getAttribute("data-private-conversation-profile") || 0,
    );
    if (!Number.isInteger(artistId) || artistId <= 0) {
      return;
    }

    closePrivateInboxDropdown();
    await openArtistProfileById(artistId);
    return;
  }

  const row = target?.closest(".private-inbox-conversation");
  if (!row) {
    return;
  }

  const otherArtistId = Number(row.getAttribute("data-other-artist-id") || 0);
  const otherArtistName =
    row.querySelector(".private-inbox-conversation-top strong")?.textContent ||
    "Artista";
  const otherArtistAvatar =
    row
      .querySelector(".private-inbox-conversation-avatar")
      ?.getAttribute("src") || "";
  if (!Number.isInteger(otherArtistId) || otherArtistId <= 0) {
    return;
  }

  await openPrivateConversation(
    otherArtistId,
    otherArtistName,
    otherArtistAvatar,
  );
});

privateInboxList?.addEventListener("submit", async (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) {
    return;
  }

  if (!form.matches("[data-private-conversation-form]")) {
    return;
  }

  event.preventDefault();
  event.stopPropagation();

  if (currentSessionUserType !== "artist") {
    showTempMessage(
      "Apenas artistas podem enviar mensagens privadas.",
      "warning",
    );
    return;
  }

  if (currentSessionArtistIsPending) {
    showTempMessage(
      "A tua conta está pendente. Não podes enviar mensagens privadas.",
      "warning",
    );
    return;
  }

  if (
    !Number.isInteger(currentPrivateConversationArtistId) ||
    currentPrivateConversationArtistId <= 0
  ) {
    showTempMessage("Conversa inválida.", "warning");
    return;
  }

  const input = form.querySelector("[data-private-conversation-input]");
  const rawMessageText =
    input instanceof HTMLTextAreaElement ? input.value.trim() : "";
  const pendingAudio = getPrivateConversationPendingAudio(
    currentPrivateConversationArtistId,
  );
  const messageText = pendingAudio?.source === "voice_recording" ? "" : rawMessageText;

  if (!messageText && !pendingAudio) {
    showTempMessage("Escreve uma mensagem ou anexa um áudio antes de enviar.", "warning");
    return;
  }

  if (messageText.length > 255) {
    showTempMessage("A mensagem deve ter no máximo 255 caracteres.", "warning");
    return;
  }

  if (pendingAudio && Number(pendingAudio.sizeBytes || 0) > PRIVATE_AUDIO_MAX_BYTES) {
    showTempMessage("O ficheiro de áudio deve ter no máximo 10 MB.", "warning");
    return;
  }

  if (
    pendingAudio?.source === "voice_recording" &&
    Number.isFinite(pendingAudio.durationSeconds) &&
    Number(pendingAudio.durationSeconds) > PRIVATE_VOICE_MAX_DURATION_SECONDS
  ) {
    showTempMessage("A mensagem de voz pode ter no máximo 1 minuto.", "warning");
    return;
  }

  try {
    await submitPrivateMessage(
      currentPrivateConversationArtistId,
      messageText,
      pendingAudio,
    );

    if (input instanceof HTMLTextAreaElement) {
      input.value = "";
    }

    setPrivateConversationDraft(currentPrivateConversationArtistId, "");
    clearPrivateConversationPendingAudio(currentPrivateConversationArtistId);
    refreshPrivateConversationComposerUi();

    await fetchPrivateConversationMessages(currentPrivateConversationArtistId, {
      silent: true,
      markRead: true,
    });
  } catch (error) {
    showTempMessage(
      error.message || "Erro ao enviar mensagem privada.",
      "error",
      3000,
    );
  }
});

privateInboxList?.addEventListener("input", (event) => {
  const target = event.target;
  if (!(target instanceof HTMLTextAreaElement)) {
    return;
  }

  if (!target.matches("[data-private-conversation-input]")) {
    return;
  }

  setPrivateConversationDraft(currentPrivateConversationArtistId, target.value);

  autoResizePrivateConversationInput(target);
});

privateInboxList?.addEventListener("change", async (event) => {
  const target = event.target;
  if (!(target instanceof HTMLInputElement)) {
    return;
  }

  if (!target.matches("[data-private-audio-input]")) {
    return;
  }

  const file = target.files?.[0];
  target.value = "";

  if (!file) {
    return;
  }

  await attachPrivateConversationAudioFile(file);
});

privateInboxList?.addEventListener("keydown", (event) => {
  const target = event.target;
  if (!(target instanceof HTMLTextAreaElement)) {
    return;
  }

  if (!target.matches("[data-private-conversation-input]")) {
    return;
  }

  if (event.key !== "Enter" || event.shiftKey || event.isComposing) {
    return;
  }

  event.preventDefault();

  const form = target.closest("form[data-private-conversation-form]");
  if (form instanceof HTMLFormElement) {
    form.requestSubmit();
  }
});

globalChatForm?.addEventListener("click", (event) => {
  const target = event.target instanceof Element ? event.target : null;
  const emojiItemBtn = target?.closest("[data-chat-emoji]");
  if (emojiItemBtn instanceof HTMLElement) {
    const emoji = (emojiItemBtn.getAttribute("data-chat-emoji") || "").trim();
    if (emoji) {
      insertEmojiAtTextareaCursor(globalChatInput, emoji);
    }

    return;
  }

  const emojiToggleBtn = target?.closest("[data-chat-emoji-toggle]");
  if (emojiToggleBtn instanceof HTMLElement) {
    toggleEmojiPickerByButton(emojiToggleBtn);
  }
});

globalChatInput?.addEventListener("input", () => {
  updateGlobalChatCounter();
  autoResizeGlobalChatInput();
});

globalChatInput?.addEventListener("keydown", (event) => {
  if (event.key !== "Enter" || event.shiftKey || event.isComposing) {
    return;
  }

  event.preventDefault();
  globalChatForm?.requestSubmit();
});

document.addEventListener("click", (event) => {
  const target = event.target instanceof Element ? event.target : null;
  if (target?.closest(".chat-emoji-wrap")) {
    return;
  }

  document.querySelectorAll("[data-chat-emoji-toggle]").forEach((button) => {
    closeEmojiPickerByButton(button);
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape") {
    return;
  }

  document.querySelectorAll("[data-chat-emoji-toggle]").forEach((button) => {
    closeEmojiPickerByButton(button);
  });
});

globalChatMinimizeBtn?.addEventListener("click", () => {
  toggleGlobalChatMinimized();
  updateGlobalChatPositionNearLegend();
});

globalChatMessages?.addEventListener("click", async (event) => {
  const target = event.target instanceof Element ? event.target : null;
  const authorButton = target?.closest(".global-chat-author-btn");
  if (!authorButton) {
    return;
  }

  const artistId = Number(
    authorButton.getAttribute("data-chat-artist-id") || 0,
  );
  if (!Number.isInteger(artistId) || artistId <= 0) {
    return;
  }

  await openArtistProfileById(artistId);
});

globalChatForm?.addEventListener("submit", async (event) => {
  event.preventDefault();

  if (currentSessionUserType !== "artist") {
    showTempMessage(
      "Só artistas podem enviar mensagens no chat geral.",
      "warning",
    );
    return;
  }

  if (currentSessionArtistIsPending) {
    showTempMessage(
      "A tua conta está pendente de confirmação. O chat geral ficará disponível após a confirmação da conta.",
      "warning",
    );
    return;
  }

  const messageText = (globalChatInput?.value || "").trim();
  if (!messageText) {
    showTempMessage("Escreve uma mensagem antes de enviar.", "warning");
    return;
  }

  if (messageText.length > 255) {
    showTempMessage("A mensagem deve ter no máximo 255 caracteres.", "warning");
    return;
  }

  try {
    const result = await submitGlobalChatMessage(messageText);
    const postedMessage = result?.chat_message;

    if (postedMessage) {
      appendGlobalChatMessages([postedMessage]);
    } else {
      await fetchGlobalChatMessages({ incremental: true });
    }

    if (globalChatInput) {
      globalChatInput.value = "";
    }

    updateGlobalChatCounter();
    autoResizeGlobalChatInput();
  } catch (error) {
    showTempMessage(
      error.message || "Erro ao enviar mensagem no chat.",
      "error",
      3000,
    );
  }
});

onboardingPrevBtn?.addEventListener("click", () => {
  moveOnboardingStep(-1);
});

onboardingNextBtn?.addEventListener("click", () => {
  moveOnboardingStep(1);
});

onboardingSkipBtn?.addEventListener("click", () => {
  closeOnboardingTutorial(true);
});

artistSortInput?.addEventListener("change", () => {
  syncArtistSortFromInput();
});

artistGenreInput?.addEventListener("input", () => {
  currentArtistGenreFilter = (artistGenreInput.value || "").toString().trim();
  applyArtistFiltersAndRender();
  renderArtistGenreDropdownOptions(currentArtistGenreFilter);
  showArtistGenreDropdown();
  updateArtistGenreClearButton();
});

artistGenreInput?.addEventListener("change", () => {
  currentArtistGenreFilter = (artistGenreInput.value || "").toString().trim();
  applyArtistFiltersAndRender();
  renderArtistGenreDropdownOptions(currentArtistGenreFilter);
  updateArtistGenreClearButton();
});

artistGenreInput?.addEventListener("focus", () => {
  renderArtistGenreDropdownOptions(artistGenreInput.value || "");
  showArtistGenreDropdown();
});

artistGenreClearBtn?.addEventListener("click", () => {
  if (!artistGenreInput) {
    return;
  }

  artistGenreInput.value = "";
  currentArtistGenreFilter = "";
  applyArtistFiltersAndRender();
  renderArtistGenreDropdownOptions("");
  showArtistGenreDropdown();
  updateArtistGenreClearButton();
  artistGenreInput.focus();
});

updateArtistGenreClearButton();
restoreGlobalChatMinimizeState();

document.addEventListener("click", (event) => {
  if (!artistGenreField) {
    return;
  }

  if (!artistGenreField.contains(event.target)) {
    hideArtistGenreDropdown();
  }
});

// Fecha o dropdown se o utilizador clicar fora dele
document.addEventListener("click", (e) => {
  if (!accountMenu.contains(e.target)) {
    accountDropdown.classList.remove("show");
  }

  if (privateInbox && !privateInbox.contains(e.target)) {
    closePrivateInboxDropdown();
  }
});

// ============================================
// INICIALIZAÇÃO
// ============================================

// Inicializar tema
initTheme();

// Verificar sessão do utilizador
checkUserSession().then((isLoggedIn) => {
  if (!isLoggedIn) {
    return;
  }

  // Carregar limite de Portugal ao iniciar
  const bootTasks = [loadPortugalBoundary()];
  // Carregar municípios ao iniciar
  bootTasks.push(loadAndShowMunicipios());
  // Pré-carregar distritos em background para acelerar a primeira troca
  preloadDistritosInBackground();
  // Configurar o interruptor de camadas
  setupLayerSwitch();
  // Configurar interruptor de pesquisa por nome/género
  setupSearchModeSwitch();

  // Adicionar event listener para mudanças de zoom
  map.on("zoomend", updateLabelsVisibility);

  // Garantir estado inicial correto das labels
  updateLabelsVisibility();

  Promise.allSettled(bootTasks).finally(() => {
    isInitialBootPhase = false;
    showLoading(false);
    setTimeout(() => {
      maybeStartOnboardingTutorial();
    }, 300);
  });
});

(function initContactWidget() {
  if (document.getElementById("contactWidgetOverlay")) {
    return;
  }

  const button = document.createElement("button");
  button.type = "button";
  button.className = "contact-fab";
  button.id = "contactWidgetOpenBtn";
  button.textContent = "Contacto";

  const overlay = document.createElement("div");
  overlay.className = "contact-overlay";
  overlay.id = "contactWidgetOverlay";
  overlay.innerHTML = `
    <div class="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contactWidgetTitle">
      <div class="contact-modal-header">
        <h2 class="contact-modal-title" id="contactWidgetTitle">Falar comigo</h2>
        <button type="button" class="contact-close-btn" aria-label="Fechar formulario">x</button>
      </div>
      <form class="contact-form" id="contactWidgetForm">
        <div class="contact-form-grid">
          <label>Nome<span class="required-mark">*</span>
            <input type="text" name="name" required maxlength="100" />
          </label>
          <label>Email<span class="required-mark">*</span>
            <input type="email" name="email" class="contact-email-input" required maxlength="160" />
          </label>
        </div>
        <div class="contact-form-grid">
          <label>Telefone
            <input type="text" name="phone" maxlength="40" />
          </label>
          <label>Assunto<span class="required-mark">*</span>
            <input type="text" name="subject" required maxlength="160" />
          </label>
        </div>
        <label>Mensagem<span class="required-mark">*</span>
          <textarea name="message" required maxlength="4000"></textarea>
        </label>
        <div class="contact-form-actions">
          <div class="contact-status" id="contactWidgetStatus"></div>
          <button type="submit" class="contact-submit-btn" id="contactWidgetSubmit">Enviar email</button>
        </div>
      </form>
    </div>
  `;

  document.body.appendChild(button);
  document.body.appendChild(overlay);

  const closeBtn = overlay.querySelector(".contact-close-btn");
  const form = document.getElementById("contactWidgetForm");
  const statusEl = document.getElementById("contactWidgetStatus");
  const submitBtn = document.getElementById("contactWidgetSubmit");
  const emailInput = form.querySelector('input[name="email"]');

  const lockContactEmail = (email) => {
    if (!emailInput) {
      return;
    }

    const normalizedEmail = (email || "").toString().trim();
    if (!normalizedEmail) {
      return;
    }

    form.dataset.lockedEmail = normalizedEmail;
    emailInput.value = normalizedEmail;
    emailInput.readOnly = true;
    emailInput.classList.add("is-locked");
    emailInput.setAttribute("aria-readonly", "true");
  };

  window.BeatmapApplyContactSessionEmail = lockContactEmail;

  if (currentSessionEmail) {
    lockContactEmail(currentSessionEmail);
  }

  const openModal = () => {
    overlay.classList.add("is-open");
    document.body.style.overflow = "hidden";
  };

  const closeModal = () => {
    overlay.classList.remove("is-open");
    document.body.style.overflow = "";
  };

  button.addEventListener("click", openModal);
  closeBtn.addEventListener("click", closeModal);

  overlay.addEventListener("click", (event) => {
    if (event.target === overlay) {
      closeModal();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && overlay.classList.contains("is-open")) {
      closeModal();
    }
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const formData = new FormData(form);
    const payload = {
      name: (formData.get("name") || "").toString().trim(),
      email: (formData.get("email") || "").toString().trim(),
      phone: (formData.get("phone") || "").toString().trim(),
      subject: (formData.get("subject") || "").toString().trim(),
      message: (formData.get("message") || "").toString().trim(),
    };

    statusEl.textContent = "A enviar...";
    submitBtn.disabled = true;

    try {
      const response = await fetch("/beatmap(mapa)/contact_send.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || "Falha ao enviar mensagem.");
      }

      statusEl.textContent = "Mensagem enviada com sucesso.";
      form.reset();

      const lockedEmailValue = (form.dataset.lockedEmail || "").toString();
      if (lockedEmailValue) {
        emailInput.value = lockedEmailValue;
      }

      setTimeout(() => {
        closeModal();
        statusEl.textContent = "";
      }, 1000);
    } catch (error) {
      statusEl.textContent = error.message || "Nao foi possivel enviar.";
    } finally {
      submitBtn.disabled = false;
    }
  });
})();
