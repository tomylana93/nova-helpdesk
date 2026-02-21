<script setup lang="ts">
import FilePondPluginFilePoster from 'filepond-plugin-file-poster';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import type { HTMLAttributes } from 'vue';
import { computed, ref } from 'vue';
import vueFilePond from 'vue-filepond';
import 'filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import 'filepond/dist/filepond.min.css';

import { useCsrf } from '@/composables/useCsrf';

export type UploadResponse = {
    path: string;
};

const FilePond = vueFilePond(
    FilePondPluginFilePoster,
    FilePondPluginImagePreview,
    FilePondPluginFileValidateType,
);

const props = withDefaults(
    defineProps<{
        processUrl: string;
        revertUrl?: string;
        modelValue: string | null;
        class?: HTMLAttributes['class'];
        deferDelete?: boolean;
    }>(),
    {
        modelValue: null,
        deferDelete: false,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', payload: string | null): void;
    (e: 'uploaded', payload: { url: string }): void;
    (e: 'error', payload: string): void;
    (e: 'removed', payload: { path: string }): void;
}>();

const errorMessage = ref<string | null>(null);
const { buildHeaders } = useCsrf();

const files = computed(() => {
    if (!props.modelValue) {
        return [];
    }

    return [
        {
            source: props.modelValue,
            options: {
                type: 'local',
                metadata: {
                    poster: props.modelValue,
                },
            },
        },
    ];
});

function isUploadResponse(value: unknown): value is UploadResponse {
    return (
        typeof value === 'object' &&
        value !== null &&
        'path' in value &&
        typeof (value as { path?: unknown }).path === 'string'
    );
}

function normalizeUploadResponse(response: unknown): string | null {
    if (!isUploadResponse(response)) {
        return null;
    }

    return response.path.length > 0 ? response.path : null;
}

function handleError(
    message: string,
    callback: (message: string) => void,
): void {
    errorMessage.value = message;
    emit('error', message);
    callback(message);
}

function handleDelete(
    fileId: string,
    load: () => void,
    error: (message: string) => void,
): void {
    errorMessage.value = null;

    fetch(props.revertUrl!, {
        method: 'DELETE',
        headers: buildHeaders({ 'Content-Type': 'application/json' }),
        body: JSON.stringify({ path: fileId }),
    })
        .then((response) => {
            if (!response.ok) {
                handleError(`Delete failed (${response.status})`, error);
                return;
            }

            emit('update:modelValue', null);
            load();
        })
        .catch(() => {
            handleError('Delete failed: network error', error);
        });
}

const server = computed(() => {
    return {
        process: (
            _fieldName: string,
            file: File,
            _metadata: unknown,
            load: (serverFileId: string) => void,
            error: (message: string) => void,
            progress: (
                isLengthComputable: boolean,
                loadedDataAmount: number,
                totalDataAmount: number,
            ) => void,
            abort: () => void,
        ) => {
            errorMessage.value = null;

            const request = new XMLHttpRequest();
            request.open('POST', props.processUrl);
            const headers = buildHeaders();
            Object.entries(headers).forEach(([key, value]) => {
                request.setRequestHeader(key, value);
            });

            request.upload.onprogress = (event: ProgressEvent) => {
                progress(event.lengthComputable, event.loaded, event.total);
            };

            request.onload = () => {
                if (request.status < 200 || request.status >= 300) {
                    handleError(`Upload failed (${request.status})`, error);
                    return;
                }

                let body: unknown = request.responseText;

                try {
                    body = JSON.parse(request.responseText) as unknown;
                } catch {
                    // If the backend returns a plain string, keep it.
                }

                const url = normalizeUploadResponse(body);

                if (!url) {
                    handleError('Upload failed: invalid response', error);
                    return;
                }

                emit('update:modelValue', url);
                emit('uploaded', { url });

                load(url);
            };

            request.onerror = () => {
                handleError('Upload failed: network error', error);
            };

            const formData = new FormData();
            formData.append('file', file, file.name);
            request.send(formData);

            return {
                abort: () => {
                    request.abort();
                    abort();
                },
            };
        },

        revert:
            props.revertUrl && !props.deferDelete
                ? (
                      uniqueFileId: string,
                      load: () => void,
                      error: (message: string) => void,
                  ) => {
                      handleDelete(uniqueFileId, load, error);
                  }
                : undefined,

        remove:
            props.revertUrl && !props.deferDelete
                ? (
                      source: string,
                      load: () => void,
                      error: (message: string) => void,
                  ) => {
                      handleDelete(source, load, error);
                  }
                : undefined,
    };
});

function onUpdateFiles(updatedFiles: unknown[]): void {
    if (updatedFiles.length === 0 && props.modelValue) {
        emit('removed', { path: props.modelValue });
        emit('update:modelValue', null);
        return;
    }

    if (updatedFiles.length === 0 && !props.revertUrl) {
        emit('update:modelValue', null);
    }
}
</script>

<template>
    <FilePond
        :files="files"
        :server="server"
        :allow-multiple="false"
        :allow-file-poster="true"
        :credits="[]"
        :label-idle="'Drop an image or Browse'"
        @updatefiles="onUpdateFiles"
    />
</template>