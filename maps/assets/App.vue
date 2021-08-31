<template>
    <v-app>
        <v-main>
            <v-container>
                <v-autocomplete
                    v-model="model"
                    :search-input.sync="request"
                    :items="entries"
                    item-text="text"
                    item-value="point"
                    outlined
                    no-filter
                    hide-no-data
                    hide-selected
                    label="Enter location"
                    @keyup="updateSuggestions"
                    append-icon=""
                >
                    <template v-slot:append>
                        <v-progress-circular v-if="isLoading" indeterminate color="red"></v-progress-circular>
                    </template>
                </v-autocomplete>

                <v-container>
                    <div id="map" min-width="600px" min-height="400px"></div>
                </v-container>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>

import { getSuggestions } from './api'

export default {
    name: 'App',
    data() {
        return {
            model: null,
            request: "",
            entries: [],
            timeoutHandle: null,
            isLoading: false,
            yaMap: null,
        }
    },
    async mounted() {
        ymaps.ready(() => {
            this.yaMap = new ymaps.Map("map", {
                center: [55, 37],
                zoom: 12
            })
        })
    },
    computed: {
    },
    watch: {
        model: function (val, _old) {
            const coords = [...val].reverse()
            this.yaMap.setCenter(coords)
        }
    },
    methods: {
        async updateSuggestions(e) {
            if (this.timeoutHandle != null) {
                clearTimeout(this.timeoutHandle)
            }
            const request = this.request == null ? "" : this.request.trim()
            if (request.length < 3) {
                return
            }
            this.timeoutHandle = setTimeout(
                async (e) => {
                    this.isLoading = true
                    const response = await getSuggestions(request)
                    this.entries = response.data
                    this.isLoading = false
                },
                1500
            )
        },
    }
}
</script>

<style lang="scss" scoped>
#map {
    height: 70vh;
}
</style>