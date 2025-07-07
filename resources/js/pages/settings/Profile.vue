<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User & { profile_photo_url?: string, profile_photo_path?: string };

const form = useForm({
    name: user.name,
    email: user.email,
    profile_photo: null as File | null,
});

const profilePhotoUrl = user.profile_photo_url || '';
const photoPreview = ref<string | null>(null);

const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
const maxSizeMB = 2;

const removePhoto = () => {
    if (window.confirm('Are you sure you want to remove your profile photo?')) {
        form.post(route('profile.remove-photo'), {
            preserveScroll: true,
            onSuccess: () => {
                photoPreview.value = null;
            },
        });
    }
};

const onFileInput = (event: Event) => {
    const target = event.target as HTMLInputElement | null;
    if (target && target.files && target.files[0]) {
        const file = target.files[0];
        if (!allowedTypes.includes(file.type)) {
            alert('Only JPG and PNG images are allowed.');
            return;
        }
        if (file.size > maxSizeMB * 1024 * 1024) {
            alert('Image size must be less than 2MB.');
            return;
        }
        form.profile_photo = file;
        const reader = new FileReader();
        reader.onload = e => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    const data = { ...form.data() };
    if (form.profile_photo) {
        data.profile_photo = form.profile_photo;
    }
    form.transform(() => data).patch(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            photoPreview.value = null;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="flex flex-col md:flex-row gap-6 md:gap-8 items-start md:items-stretch">
                        <div class="flex-1 grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name"
                                placeholder="Full name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                            <Label for="email" class="mt-4">Email address</Label>
                            <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                                autocomplete="username" placeholder="Email address" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div
                            class="flex flex-col items-center justify-center border min-w-[150px] dark:border-amber-300 gap-2 p-4 rounded-lg border-gray-200 ">
                            <img :src="photoPreview || profilePhotoUrl" alt="Profile Photo"
                                class="w-24 h-24 rounded-full object-cover border border-gray-300 dark:border-neutral-700 shadow-sm" />
                            <label class="flex flex-col items-center mt-2 w-full">
                                <input type="file" accept="image/jpeg,image/png"
                                    class="block self-center w-full text-sm text-gray-500 dark:text-neutral-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/80 cursor-pointer dark:file:text-black mx-auto"
                                    @input="onFileInput" />
                                <span class="block  text-xs text-muted-foreground dark:text-neutral-400 mt-1">Allowed:
                                    JPG, PNG. Max size: 2MB.</span>
                            </label>
                            <Button v-if="user.profile_photo_path" type="button" variant="destructive" class="mt-2"
                                @click="removePhoto">
                                Remove Photo
                            </Button>
                        </div>
                    </div>
                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground dark:text-neutral-400">
                            Your email address is unverified.
                            <Link :href="route('verification.send')" method="post" as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                            Click here to resend the verification email.
                            </Link>
                        </p>
                        <div v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
