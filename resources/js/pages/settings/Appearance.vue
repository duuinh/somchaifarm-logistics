<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Layout from '@/layouts/settings/Layout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { Palette } from 'lucide-vue-next';
</script>

<template>
    <Head title="รูปแบบการแสดงผล" />

    <Layout>
        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Palette class="h-5 w-5" />
                        รูปแบบการแสดงผล
                    </CardTitle>
                    <CardDescription>
                        เลือกธีมและรูปแบบการแสดงผลที่เหมาะสมกับคุณ
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium mb-3">ธีมสี</h3>
                            <AppearanceTabs />
                            <p class="text-sm text-muted-foreground mt-2">
                                เลือกธีมสีที่ต้องการ หรือใช้การตั้งค่าของระบบ
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </Layout>
</template>