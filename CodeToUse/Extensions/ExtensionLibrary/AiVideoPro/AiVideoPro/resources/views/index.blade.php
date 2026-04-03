@php
	if (setting('sora_active', 1) == 1) {
		$actions = ['sora' => __('Generate video with Sora')];
	} else {
		$actions = [];
	}

	$actions += [
		'veo3.1' => __('Generate video with Google VEO 3.1'),
		'veo3' => __('Generate video with Google VEO 3'),
		'veo3-fast' => __('Generate video with FAST Google VEO 3'),
		'veo2' => __('Generate video with Google VEO 2'),
		'luma-dream-machine' => __('Generate video with Luma'),
		'kling25Turbo' => __('Generate video with Kling 2.5 Turbo'),
		'kling' => __('Generate video with Kling'),
		'klingImage' => __('Generate video with Kling from image'),
		'klingV21' => __('Generate video with Kling V2.1'),
		'minimax' => __('Generate video with Minimax'),
	];
@endphp

@extends('panel.layout.app')
@section('title', __('AI Video Pro'))
@section('titlebar_subtitle', __('You can create amazing videos with AI Video Pro'))
@section('titlebar_actions', __(''))

@section('content')
	<div class="py-10">
		<x-card
			class="lqd-video-generator border-0 bg-[#F2F1FD] dark:bg-surface"
			size="lg"
			x-data="{ selectedAction: '{{ array_key_first($actions) }}', veo31Mode: 'text-to-video' }"
		>
			{{-- Replace the form content with this improved version --}}

			<form
				class="flex flex-col gap-4"
				id="photo-studio-form"
				method="post"
				action="{{ route('dashboard.user.ai-video-pro.store') }}"
				enctype="multipart/form-data"
				x-data="{ selectedAction: '{{ array_key_first($actions) }}', veo31Mode: 'text-to-video', kling25Mode: 'text-to-video-pro' }"
			>
				@csrf

				<h3>{{ __('Choose an Action') }}</h3>
				<x-forms.input
					class="truncate"
					id="action"
					name="action"
					type="select"
					label="{{ __('Select Action') }}"
					size="lg"
					x-model="selectedAction"
				>
					@foreach ($actions as $value => $label)
						<option value="{{ $value }}">
							{{ __($label) }}
						</option>
					@endforeach
				</x-forms.input>

				{{-- Image Upload for Kling, Sora, and VEO 3.1 Image-to-Video modes --}}
				<div
					class="flex w-full flex-col gap-5"
					x-show="['klingImage', 'klingV21', 'sora'].includes(selectedAction) || (selectedAction === 'veo3.1' && ['image-to-video', 'image-to-video-fast'].includes(veo31Mode)) || (selectedAction === 'kling25Turbo' && ['image-to-video', 'image-to-video-pro'].includes(kling25Mode))"
					x-cloak
					ondrop="dropHandler(event, 'img2img_src');"
					ondragover="dragOverHandler(event);"
				>
					<template x-if="selectedAction === 'sora'">
						<label class="text-xs text-foreground">
							{{ __('Reference Image (optional) — must match the requested width and height') }}
							<x-info-tooltip text="{{ __('The size must be one of the following: 720x1280, 1280x720, 1024x1792, 1792x1024') }}" />
						</label>
					</template>

					<template x-if="selectedAction === 'veo3.1' && ['image-to-video', 'image-to-video-fast'].includes(veo31Mode)">
						<label class="text-xs text-foreground">
							{{ __('Upload Image for Image-to-Video (720p or higher)') }}
						</label>
					</template>

					<template x-if="selectedAction === 'kling25Turbo' && ['image-to-video', 'image-to-video-pro'].includes(kling25Mode)">
						<label class="text-xs text-foreground">
							{{ __('Upload Image for Image-to-Video') }}
						</label>
					</template>

					<label
						class="lqd-filepicker-label flex min-h-34 w-full cursor-pointer flex-col items-center justify-center rounded-card border-2 border-dashed border-foreground/10 bg-background text-center transition-colors hover:bg-background/80"
						for="img2img_src"
					>
						<div class="flex flex-col items-center justify-center py-6">
							<x-tabler-cloud-upload
								class="mb-4 size-11"
								stroke-width="1.5"
							/>
							<p class="mb-1 text-sm font-semibold">
								{{ __('Drop your image here or browse.') }}
							</p>
							<p class="file-name mb-0 text-2xs">
								{{ __('(Only jpg, png will be accepted)') }}
							</p>
						</div>
						<input
							class="hidden"
							id="img2img_src"
							name="image_url"
							type="file"
							accept=".png, .jpg, .jpeg"
							onchange="handleFileSelect('img2img_src')"
							:disabled="!((['klingImage', 'klingV21', 'sora'].includes(selectedAction)) || (selectedAction === 'veo3.1' && ['image-to-video', 'image-to-video-fast'].includes(veo31Mode)) || (selectedAction === 'kling25Turbo' && ['image-to-video', 'image-to-video-pro'].includes(kling25Mode)))"
						/>
					</label>
				</div>

				{{-- Prompt Input --}}
				<x-forms.input
					class="mt-4"
					id="prompt"
					name="prompt"
					label="{{ __('Prompt') }}"
					size="lg"
					rows="4"
					type="textarea"
					required
				/>

				{{-- Sora Options --}}
				<template x-if="selectedAction === 'sora'">
					<div class="flex flex-wrap gap-6">
						<x-forms.input
							id="sora_model"
							label="{{ __('Model') }}"
							name="sora_model"
							type="select"
							size="lg"
						>
							<option value="{{ \App\Domains\Entity\Enums\EntityEnum::SORA_2->value }}">
								{{ __(\App\Domains\Entity\Enums\EntityEnum::SORA_2->label()) }}
							</option>
							<option value="{{ \App\Domains\Entity\Enums\EntityEnum::SORA_2_PRO->value }}">
								{{ __(\App\Domains\Entity\Enums\EntityEnum::SORA_2_PRO->label()) }}
							</option>
						</x-forms.input>

						<x-forms.input
							id="sora_seconds"
							label="{{ __('Duration') }}"
							name="sora_seconds"
							type="select"
							size="lg"
							tooltip="{{ __('Select video duration (4, 8, or 12 seconds).') }}"
						>
							@foreach ([4, 8, 12] as $seconds)
								<option value="{{ $seconds }}" {{ $seconds === 4 ? 'selected' : '' }}>
									{{ $seconds }} {{ __('seconds') }}
								</option>
							@endforeach
						</x-forms.input>

						<div class="flex-1">
							<x-forms.input
								id="sora_size"
								label="{{ __('Size') }}"
								name="sora_size"
								type="select"
								size="lg"
							>
								@foreach (['720x1280', '1280x720', '1024×1792', '1792×1024'] as $size)
									<option value="{{ $size }}" {{ $size === '720x1280' ? 'selected' : '' }}>
										{{ $size }}
									</option>
								@endforeach
							</x-forms.input>
						</div>
					</div>
				</template>

				{{-- VEO 3.1 Options --}}
				<template x-if="selectedAction === 'veo3.1'">
					<div class="flex flex-col gap-4">
						{{-- VEO 3.1 Mode Selector --}}
						<x-forms.input
							id="veo3_1_mode"
							name="veo3_1_mode"
							label="{{ __('VEO 3.1 Mode') }}"
							type="select"
							size="lg"
							x-model="veo31Mode"
							tooltip="{{ __('Choose which generation mode to use for Google VEO 3.1') }}"
						>
							<option value="text-to-video">{{ __('Text to Video') }}</option>
							<option value="text-to-video-fast">{{ __('Text to Video (Fast)') }}</option>
							<option value="first-last-frame-to-video">{{ __('First/Last Frame to Video') }}</option>
							<option value="first-last-frame-to-video-fast">{{ __('First/Last Frame to Video (Fast)') }}</option>
							<option value="image-to-video">{{ __('Image to Video') }}</option>
							<option value="image-to-video-fast">{{ __('Image to Video (Fast)') }}</option>
							<option value="reference-to-video">{{ __('Reference to Video') }}</option>
						</x-forms.input>

						{{-- First/Last Frame Uploads --}}
						<template x-if="['first-last-frame-to-video', 'first-last-frame-to-video-fast'].includes(veo31Mode)">
							<div class="flex flex-col gap-4">
								<div>
									<label class="text-xs text-foreground mb-2 block">
										{{ __('First Frame Image') }}
										<x-info-tooltip text="{{ __('The first frame of your video') }}" />
									</label>
									<label
										class="lqd-filepicker-label flex min-h-34 w-full cursor-pointer flex-col items-center justify-center rounded-card border-2 border-dashed border-foreground/10 bg-background text-center transition-colors hover:bg-background/80"
										for="veo3_1_first_frame"
										ondrop="dropHandler(event, 'veo3_1_first_frame');"
										ondragover="dragOverHandler(event);"
									>
										<div class="flex flex-col items-center justify-center py-6">
											<x-tabler-cloud-upload class="mb-4 size-11" stroke-width="1.5" />
											<p class="mb-1 text-sm font-semibold">{{ __('Drop your first frame here or browse.') }}</p>
											<p class="file-name mb-0 text-2xs">{{ __('(Only jpg, png accepted)') }}</p>
										</div>
										<input
											class="hidden"
											id="veo3_1_first_frame"
											name="first_frame_image_url"
											type="file"
											accept=".png,.jpg,.jpeg"
											onchange="handleFileSelect('veo3_1_first_frame')"
											:disabled="!['first-last-frame-to-video', 'first-last-frame-to-video-fast'].includes(veo31Mode)"
										/>
									</label>
								</div>
								<div>
									<label class="text-xs text-foreground mb-2 block">
										{{ __('Last Frame Image') }}
										<x-info-tooltip text="{{ __('The last frame of your video') }}" />
									</label>
									<label
										class="lqd-filepicker-label flex min-h-34 w-full cursor-pointer flex-col items-center justify-center rounded-card border-2 border-dashed border-foreground/10 bg-background text-center transition-colors hover:bg-background/80"
										for="veo3_1_last_frame"
										ondrop="dropHandler(event, 'veo3_1_last_frame');"
										ondragover="dragOverHandler(event);"
									>
										<div class="flex flex-col items-center justify-center py-6">
											<x-tabler-cloud-upload class="mb-4 size-11" stroke-width="1.5" />
											<p class="mb-1 text-sm font-semibold">{{ __('Drop your last frame here or browse.') }}</p>
											<p class="file-name mb-0 text-2xs">{{ __('(Only jpg, png accepted)') }}</p>
										</div>
										<input
											class="hidden"
											id="veo3_1_last_frame"
											name="last_frame_image_url"
											type="file"
											accept=".png,.jpg,.jpeg"
											onchange="handleFileSelect('veo3_1_last_frame')"
											:disabled="!['first-last-frame-to-video', 'first-last-frame-to-video-fast'].includes(veo31Mode)"
										/>
									</label>
								</div>
							</div>
						</template>

						{{-- Image Upload for Image-to-Video modes --}}
						<template x-if="['image-to-video', 'image-to-video-fast'].includes(veo31Mode)">
							<div>
								<label class="text-xs text-foreground mb-2 block">
									{{ __('Input Image') }}
									<x-info-tooltip text="{{ __('The image to animate') }}" />
								</label>
								<label
									class="lqd-filepicker-label flex min-h-34 w-full cursor-pointer flex-col items-center justify-center rounded-card border-2 border-dashed border-foreground/10 bg-background text-center transition-colors hover:bg-background/80"
									for="veo3_1_image"
									ondrop="dropHandler(event, 'veo3_1_image');"
									ondragover="dragOverHandler(event);"
								>
									<div class="flex flex-col items-center justify-center py-6">
										<x-tabler-cloud-upload class="mb-4 size-11" stroke-width="1.5" />
										<p class="mb-1 text-sm font-semibold">{{ __('Drop your image here or browse.') }}</p>
										<p class="file-name mb-0 text-2xs">{{ __('(Only jpg, png accepted)') }}</p>
									</div>
									<input
										class="hidden"
										id="veo3_1_image"
										name="image_url"
										type="file"
										accept=".png,.jpg,.jpeg"
										onchange="handleFileSelect('veo3_1_image')"
										:disabled="!['image-to-video', 'image-to-video-fast'].includes(veo31Mode)"
									/>
								</label>
							</div>
						</template>

						{{-- Reference Images Upload (1-3 images) --}}
						<template x-if="veo31Mode === 'reference-to-video'">
							<div>
								<label class="text-xs text-foreground mb-2 block">
									{{ __('Upload Reference Images (1-3 images to guide style/subject)') }}
									<x-info-tooltip text="{{ __('Upload 1-3 reference images that will guide the visual style and subject of the generated video') }}" />
								</label>
								<label
									class="lqd-filepicker-label flex min-h-34 w-full cursor-pointer flex-col items-center justify-center rounded-card border-2 border-dashed border-foreground/10 bg-background text-center transition-colors hover:bg-background/80"
									for="veo3_1_reference_images"
								>
									<div class="flex flex-col items-center justify-center py-6">
										<x-tabler-cloud-upload class="mb-4 size-11" stroke-width="1.5" />
										<p class="mb-1 text-sm font-semibold">{{ __('Drop your reference images here or browse.') }}</p>
										<p class="file-name mb-0 text-2xs">{{ __('(1-3 images, jpg/png accepted)') }}</p>
									</div>
									<input
										class="hidden"
										id="veo3_1_reference_images"
										name="image_urls[]"
										type="file"
										accept=".png,.jpg,.jpeg"
										multiple
										onchange="handleFileSelect('veo3_1_reference_images')"
									/>
								</label>
							</div>
						</template>

						{{-- Flex container for Duration, Resolution, and Aspect Ratio --}}
						<div class="flex flex-col gap-4 lg:flex-row">
							{{-- Duration: Conditional select based on mode --}}
							<template x-if="['text-to-video', 'text-to-video-fast'].includes(veo31Mode)">
								<x-forms.input
									class:container="flex-1"
									id="veo3_1_duration"
									name="duration"
									label="{{ __('Duration') }}"
									type="select"
									size="lg"
									tooltip="{{ __('Video duration (4-8 seconds)') }}"
								>
									<option value="4s">4s</option>
									<option value="6s">6s</option>
									<option value="8s" selected>8s</option>
								</x-forms.input>
							</template>

							<template x-if="!['text-to-video', 'text-to-video-fast'].includes(veo31Mode)">
								<x-forms.input
									class:container="flex-1"
									id="veo3_1_duration"
									name="duration"
									label="{{ __('Duration') }}"
									type="select"
									size="lg"
									tooltip="{{ __('Video duration (8 seconds only for this mode)') }}"
								>
									<option value="8s" selected>{{ __('8s (Only option for this mode)') }}</option>
								</x-forms.input>
							</template>

							<x-forms.input
								class:container="flex-1"
								id="veo3_1_resolution"
								name="resolution"
								label="{{ __('Resolution') }}"
								type="select"
								size="lg"
								tooltip="{{ __('Video resolution quality') }}"
							>
								<option value="720p" selected>720p</option>
								<option value="1080p">1080p</option>
							</x-forms.input>

							{{-- Aspect Ratio: Text-to-Video modes --}}
							<template x-if="['text-to-video', 'text-to-video-fast'].includes(veo31Mode)">
								<x-forms.input
									class:container="flex-1"
									id="veo3_1_aspect_ratio"
									name="aspect_ratio"
									label="{{ __('Aspect Ratio') }}"
									type="select"
									size="lg"
									tooltip="{{ __('Aspect ratio of the output video') }}"
								>
									<option value="16:9" selected>16:9 (Landscape)</option>
									<option value="9:16">9:16 (Portrait)</option>
									<option value="1:1">1:1 (Square - will be outpainted)</option>
								</x-forms.input>
							</template>

							{{-- Aspect Ratio: First/Last Frame modes --}}
							<template x-if="['first-last-frame-to-video', 'first-last-frame-to-video-fast'].includes(veo31Mode)">
								<x-forms.input
									class:container="flex-1"
									id="veo3_1_aspect_ratio"
									name="aspect_ratio"
									label="{{ __('Aspect Ratio') }}"
									type="select"
									size="lg"
									tooltip="{{ __('Aspect ratio of the output video') }}"
								>
									<option value="auto" selected>Auto (Detect from images)</option>
									<option value="16:9">16:9 (Landscape)</option>
									<option value="9:16">9:16 (Portrait)</option>
									<option value="1:1">1:1 (Square - will be outpainted)</option>
								</x-forms.input>
							</template>

							{{-- Aspect Ratio: Image-to-Video modes --}}
							<template x-if="['image-to-video', 'image-to-video-fast'].includes(veo31Mode)">
								<x-forms.input
									class:container="flex-1"
									id="veo3_1_aspect_ratio"
									name="aspect_ratio"
									label="{{ __('Aspect Ratio') }}"
									type="select"
									size="lg"
									tooltip="{{ __('Aspect ratio of the output video') }}"
								>
									<option value="16:9" selected>16:9 (Landscape)</option>
									<option value="9:16">9:16 (Portrait)</option>
								</x-forms.input>
							</template>

							{{-- No Aspect Ratio for reference-to-video mode --}}
						</div>

						{{-- Generate Audio Toggle --}}
						<div class="flex w-full items-center justify-between rounded-md border p-3">
							<div class="flex gap-3">
								<span class="text-xs font-medium text-heading-foreground">@lang('Generate Audio')</span>
								<x-info-tooltip text="{{ __('If disabled, video will be silent (saves 33% credits)') }}" />
							</div>
							<x-forms.input
								class="bg-foreground/30 checked:bg-primary"
								type="checkbox"
								name="generate_audio"
								switcher
								checked
							/>
						</div>

						{{-- Advanced Options: Only for text-to-video modes --}}
						<template x-if="['text-to-video', 'text-to-video-fast'].includes(veo31Mode)">
							<div x-data="{ showAdvanced: false }">
								<x-button
									class="flex w-full items-center justify-between gap-7 py-3 text-2xs"
									type="button"
									variant="link"
									@click="showAdvanced = !showAdvanced"
								>
									<span class="h-px grow bg-current opacity-10"></span>
									<span class="flex items-center gap-3">
						{{ __('Advanced Options') }}
						<x-tabler-chevron-down class="size-4 transition" ::class="{ 'rotate-180': showAdvanced }" />
					</span>
									<span class="h-px grow bg-current opacity-10"></span>
								</x-button>

								<div class="hidden pt-5" :class="{ hidden: !showAdvanced }">
									<x-forms.input
										id="veo3_1_seed"
										name="seed"
										label="{{ __('Seed') }}"
										tooltip="{{ __('Optional: numeric seed for deterministic results') }}"
										size="lg"
										type="number"
										placeholder="{{ __('Leave empty for random') }}"
									/>

									<x-forms.input
										class:container="mt-4"
										id="veo3_1_negative_prompt"
										name="negative_prompt"
										label="{{ __('Negative Prompt') }}"
										tooltip="{{ __('Specify what to avoid in the video') }}"
										size="lg"
										rows="3"
										type="textarea"
										placeholder="{{ __('e.g., blurry, distorted, low quality') }}"
									/>

									<div class="flex w-full items-center justify-between rounded-md border p-3 mt-4">
										<div class="flex gap-3">
											<span class="text-xs font-medium text-heading-foreground">@lang('Enhance Prompt')</span>
											<x-info-tooltip text="{{ __('Improves your prompt for higher-quality results') }}" />
										</div>
										<x-forms.input
											class="bg-foreground/30 checked:bg-primary"
											type="checkbox"
											name="enhance_prompt"
											switcher
											checked
										/>
									</div>

									<div class="flex w-full items-center justify-between rounded-md border p-3 mt-4">
										<div class="flex gap-3">
											<span class="text-xs font-medium text-heading-foreground">@lang('Auto Fix Prompts')</span>
											<x-info-tooltip text="{{ __('Automatically rewrite prompts that fail content policy') }}" />
										</div>
										<x-forms.input
											class="bg-foreground/30 checked:bg-primary"
											type="checkbox"
											name="auto_fix"
											switcher
											checked
										/>
									</div>
								</div>
							</div>
						</template>
					</div>
				</template>

				{{-- Kling 2.5 Turbo Options --}}
				<template x-if="selectedAction === 'kling25Turbo'">
					<div class="flex flex-col gap-4">
						{{-- Kling 2.5 Turbo Mode Selector --}}
						<x-forms.input
							id="kling25_mode"
							name="kling25_mode"
							label="{{ __('Kling 2.5 Turbo Mode') }}"
							type="select"
							size="lg"
							x-model="kling25Mode"
							tooltip="{{ __('Choose generation mode for Kling 2.5 Turbo') }}"
						>
							<option value="text-to-video-pro">{{ __('Text to Video (Pro)') }}</option>
							<option value="image-to-video">{{ __('Image to Video (Standard)') }}</option>
							<option value="image-to-video-pro">{{ __('Image to Video (Pro)') }}</option>
						</x-forms.input>

						{{-- Flex container for Duration and Aspect Ratio --}}
						<div class="flex flex-col gap-4 lg:flex-row">
							<x-forms.input
								class:container="flex-1"
								id="kling25_duration"
								name="kling25turbo_duration"
								label="{{ __('Duration') }}"
								type="select"
								size="lg"
								tooltip="{{ __('Video duration (5 or 10 seconds)') }}"
							>
								<option value="5" selected>5s</option>
								<option value="10">10s</option>
							</x-forms.input>

							<x-forms.input
								class:container="flex-1"
								id="kling25_aspect_ratio"
								name="kling25turbo_aspect_ratio"
								label="{{ __('Aspect Ratio') }}"
								type="select"
								size="lg"
								tooltip="{{ __('Aspect ratio of the output video') }}"
							>
								<option value="16:9" selected>16:9 (Landscape)</option>
								<option value="9:16">9:16 (Portrait)</option>
								<option value="1:1">1:1 (Square)</option>
							</x-forms.input>
						</div>

						{{-- Camera Movement (Text-to-Video modes only) --}}
						<template x-if="kling25Mode === 'text-to-video-pro'">
							<x-forms.input
								id="kling25_camera_movement"
								name="camera_movement"
								label="{{ __('Camera Movement') }}"
								type="select"
								size="lg"
								tooltip="{{ __('Optional: Control camera movement in the generated video') }}"
							>
								<option value="">{{ __('None (Auto)') }}</option>
								<option value="horizontal">{{ __('Horizontal') }}</option>
								<option value="vertical">{{ __('Vertical') }}</option>
								<option value="pan">{{ __('Pan') }}</option>
								<option value="tilt">{{ __('Tilt') }}</option>
								<option value="zoom">{{ __('Zoom') }}</option>
								<option value="roll">{{ __('Roll') }}</option>
							</x-forms.input>
						</template>

						{{-- Advanced Options --}}
						<div x-data="{ showAdvanced: false }">
							<x-button
								class="flex w-full items-center justify-between gap-7 py-3 text-2xs"
								type="button"
								variant="link"
								@click="showAdvanced = !showAdvanced"
							>
								<span class="h-px grow bg-current opacity-10"></span>
								<span class="flex items-center gap-3">
						{{ __('Advanced Options') }}
						<x-tabler-chevron-down class="size-4 transition" ::class="{ 'rotate-180': showAdvanced }" />
					</span>
								<span class="h-px grow bg-current opacity-10"></span>
							</x-button>

							<div class="hidden pt-5" :class="{ hidden: !showAdvanced }">
								{{-- Seed --}}
								<x-forms.input
									id="kling25_seed"
									name="seed"
									label="{{ __('Seed') }}"
									tooltip="{{ __('Optional: numeric seed for deterministic results (0-2147483647)') }}"
									size="lg"
									type="number"
									min="0"
									max="2147483647"
									placeholder="{{ __('Leave empty for random') }}"
								/>

								{{-- Negative Prompt --}}
								<x-forms.input
									class:container="mt-4"
									id="kling25_negative_prompt"
									name="negative_prompt"
									label="{{ __('Negative Prompt') }}"
									tooltip="{{ __('Specify what to avoid in the video') }}"
									size="lg"
									rows="3"
									type="textarea"
									placeholder="{{ __('e.g., blurry, distorted, low quality') }}"
								/>

								{{-- CFG Scale (Text-to-Video modes only) --}}
								<template x-if="kling25Mode === 'text-to-video-pro'">
									<x-forms.input
										class:container="mt-4"
										id="kling25_cfg_scale"
										name="cfg_scale"
										label="{{ __('CFG Scale') }}"
										tooltip="{{ __('Guidance scale (0.0-1.0). Higher values follow prompt more closely. Default: 0.5') }}"
										size="lg"
										type="number"
										step="0.1"
										min="0"
										max="1"
										placeholder="0.5"
									/>
								</template>

								{{-- Loop (Pro mode only for 5s videos) --}}
								<template x-if="kling25Mode.includes('pro')">
									<div class="flex w-full items-center justify-between rounded-md border p-3 mt-4">
										<div class="flex gap-3">
											<span class="text-xs font-medium text-heading-foreground">@lang('Loop Video')</span>
											<x-info-tooltip text="{{ __('Make video seamlessly loop (only for 5s duration in Pro mode)') }}" />
										</div>
										<x-forms.input
											class="bg-foreground/30 checked:bg-primary"
											type="checkbox"
											name="loop"
											switcher
										/>
									</div>
								</template>
							</div>
						</div>
					</div>
				</template>

				{{-- VEO 3 / VEO 3 Fast / VEO 2 Options --}}
				<template x-if="['veo3', 'veo3-fast', 'veo2'].includes(selectedAction)">
					<div class="flex flex-col gap-4">
						<x-forms.input
							label="{{ __('Aspect Ratio') }}"
							name="aspect_ratio"
							size="lg"
							type="select"
							tooltip="{{ __('The aspect ratio of the video.') }}"
						>
							@foreach (\App\Packages\FalAI\Enums\AspectRatioEnum::cases() as $ratio)
								<option value="{{ $ratio->value }}" {{ $ratio->value === '16:9' ? 'selected' : '' }}>
									{{ $ratio->label() }}
								</option>
							@endforeach
						</x-forms.input>

						<x-forms.input
							id="veo3_duration"
							name="duration"
							label="{{ __('Duration') }}"
							type="select"
							size="lg"
							tooltip="{{ __('Select video duration (VEO 3: 4-8s, VEO 2: 5s fixed)') }}"
						>
							<template x-if="selectedAction === 'veo2'">
								<option value="5s" selected>5s</option>
							</template>
							<template x-if="['veo3', 'veo3-fast'].includes(selectedAction)">
								@foreach (['4s', '6s', '8s'] as $dur)
									<option value="{{ $dur }}" {{ $dur === '8s' ? 'selected' : '' }}>
										{{ $dur }}
									</option>
								@endforeach
							</template>
						</x-forms.input>

						<div class="flex flex-col gap-4 lg:flex-row lg:items-center">
							<div class="flex w-full items-center justify-between rounded-md border p-3">
								<div class="flex gap-3">
									<span class="text-xs font-medium text-heading-foreground">@lang('Enhance Prompt')</span>
									<x-info-tooltip text="{{ __('Enable this to improve your prompt for better results') }}" />
								</div>
								<x-forms.input
									class="bg-foreground/30 checked:bg-primary"
									type="checkbox"
									switcher
									name="enhance_prompt"
								/>
							</div>
							<div class="flex w-full items-center justify-between rounded-md border p-3">
								<div class="flex gap-3">
									<span class="text-xs font-medium text-heading-foreground">@lang('Generate Audio')</span>
									<x-info-tooltip text="{{ __('If disabled, video will be silent (saves ~33% credits)') }}" />
								</div>
								<x-forms.input
									class="bg-foreground/30 checked:bg-primary"
									type="checkbox"
									name="generate_audio"
									switcher
									checked
								/>
							</div>
						</div>

						<div x-data="{ showContent: false }">
							<x-button
								class="flex w-full items-center justify-between gap-7 py-3 text-2xs"
								type="button"
								variant="link"
								@click="showContent = !showContent"
							>
								<span class="h-px grow bg-current opacity-10"></span>
								<span class="flex items-center gap-3">
						{{ __('Advanced Options') }}
						<x-tabler-chevron-down class="size-4 transition" ::class="{ 'rotate-180': showContent }" />
					</span>
								<span class="h-px grow bg-current opacity-10"></span>
							</x-button>
							<div class="hidden pt-5" :class="{ hidden: !showContent }">
								<x-forms.input
									id="seed"
									name="seed"
									label="{{ __('Seed') }}"
									tooltip="{{ __('Set a numeric seed for consistent and repeatable results') }}"
									size="lg"
									type="number"
									placeholder="{{ __('Leave empty for random') }}"
								/>

								<div class="flex w-full items-center justify-between rounded-md border p-3 mt-4">
									<div class="flex gap-3">
										<span class="text-xs font-medium text-heading-foreground">@lang('Auto Fix Prompts')</span>
										<x-info-tooltip text="{{ __('Automatically rewrite prompts that fail content policy') }}" />
									</div>
									<x-forms.input
										class="bg-foreground/30 checked:bg-primary"
										type="checkbox"
										name="auto_fix"
										switcher
										checked
									/>
								</div>
							</div>
						</div>
					</div>
				</template>

				@if (\App\Helpers\Classes\Helper::appIsDemo())
					<x-button
						class="openai_generator_button mt-4 w-full"
						onclick="toastr.info('This feature is disabled in the demo version.')"
						size="lg"
						type="button"
					>
						{{ __('Generate') }}
						<x-tabler-arrow-right class="size-5" />
					</x-button>
				@else
					<x-button
						class="openai_generator_button mt-4 w-full"
						size="lg"
						type="submit"
					>
						{{ __('Generate') }}
						<x-tabler-arrow-right class="size-5" />
					</x-button>
				@endif
			</form>
		</x-card>

		<div class="lqd-ai-videos-wrap" id="lqd-ai-videos-wrap">
			<svg width="0" height="0">
				<defs>
					<linearGradient
						id="loader-spinner-gradient"
						x1="0.667969"
						y1="6.10667"
						x2="23.0413"
						y2="25.84"
						gradientUnits="userSpaceOnUse"
					>
						<stop stop-color="#82E2F4" />
						<stop offset="0.502" stop-color="#8A8AED" />
						<stop offset="1" stop-color="#6977DE" />
					</linearGradient>
				</defs>
			</svg>

			@if (filled($list))
				<h3 class="my-8">@lang('My Videos')</h3>
			@else
				<h2 class="col-span-full my-8 flex items-center justify-center">
					@lang('No videos found.')
				</h2>
			@endif

			<div id="videos-container">
				@include('ai-video-pro::videos-list', ['list' => $list])
			</div>
		</div>
	</div>

	<template id="image_result">
		<div class="image-result lqd-loading-skeleton lqd-is-loading group w-full">
			<figure
				class="lqd-image-result-fig relative mb-3 aspect-square overflow-hidden rounded-lg shadow-md transition-all group-hover:-translate-y-1 group-hover:scale-105 group-hover:shadow-lg"
				data-lqd-skeleton-el
			>
				<img
					class="lqd-image-result-img aspect-square h-full w-full object-cover object-center"
					loading="lazy"
					src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgc3R5bGU9ImZpbGw6I2VlZWVlZTsiLz48L3N2Zz4="
				>
				<div
					class="lqd-image-result-actions absolute inset-0 flex w-full flex-col items-center justify-center gap-2 p-4 transition-opacity group-[&.lqd-is-loading]:invisible group-[&.lqd-is-loading]:opacity-0">
					<div class="opacity-0 transition-opacity group-hover:opacity-100">
						<x-button
							class="lqd-image-result-download download size-9 rounded-full bg-background text-foreground hover:bg-background hover:bg-emerald-400 hover:text-white"
							size="none"
							href="#"
							download=true
						>
							<x-tabler-download class="size-5" />
						</x-button>
						<x-button
							class="lqd-image-result-view gallery size-9 bg-background text-foreground hover:bg-background hover:bg-emerald-400 hover:text-white"
							@click.prevent="setActiveItem( JSON.parse($el.getAttribute('data-payload') || {}) ); modalShow = true"
							size="none"
							href="#"
						>
							<x-tabler-eye class="size-5" />
						</x-button>
						<x-button
							class="lqd-image-result-delete delete size-9 rounded-full bg-background text-foreground hover:bg-background hover:bg-red-500 hover:text-white"
							size="none"
							onclick="return confirm('Are you sure?')"
							href="#"
						>
							<x-tabler-x class="size-4" />
						</x-button>
					</div>
					<span
						class="lqd-image-result-type absolute bottom-4 end-4 mb-0 rounded-full bg-background px-2 py-1 text-3xs font-semibold uppercase leading-none transition-opacity group-[&.lqd-is-loading]:invisible group-[&[data-generator=de]]:text-red-500 group-[&[data-generator=sd]]:text-blue-500 group-[&.lqd-is-loading]:opacity-0"
					></span>
				</div>
			</figure>
			<p
				class="lqd-image-result-title mb-1 w-full overflow-hidden overflow-ellipsis whitespace-nowrap text-heading-foreground transition-opacity"
				data-lqd-skeleton-el
			></p>
		</div>
	</template>
@endsection

@push('script')
	<script>
		function dropHandler(ev, id) {
			ev.preventDefault();
			$('#' + id)[0].files = ev.dataTransfer.files;
			if (typeof resizeImage === 'function') {
				resizeImage();
			}
			const fileName = ev.dataTransfer.files.length > 1
				? ev.dataTransfer.files.length + ' files selected'
				: ev.dataTransfer.files[0].name;
			$('#' + id).closest('label').find(".file-name").text(fileName);
		}

		function dragOverHandler(ev) {
			ev.preventDefault();
		}

		function handleFileSelect(id) {
			const files = $('#' + id)[0].files;
			const fileName = files.length > 1
				? files.length + ' files selected'
				: files[0].name;
			$('#' + id).closest('label').find(".file-name").text(fileName);
		}
	</script>
	<script>
		function checkVideoStatus() {
			fetch('{!! route('dashboard.user.ai-video-pro.check', ['ids' => $inProgress]) !!}')
				.then(response => response.json())
				.then(data => {
					for (const [id, item] of Object.entries(data.data)) {
						let videoElement = document.getElementById(item.divId);
						if (videoElement) {
							videoElement.innerHTML = item.html;
						}
					}
				})
				.catch(error => console.error('Error:', error));
		}

		document.addEventListener('DOMContentLoaded', function() {
			setInterval(checkVideoStatus, 5000);
		});
	</script>
@endpush
