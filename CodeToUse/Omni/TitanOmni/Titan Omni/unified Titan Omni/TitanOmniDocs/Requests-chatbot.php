<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatbotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-chatbots');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:ext_chatbots,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'ai_model' => [
                'required',
                'string',
                'in:claude-opus,claude-sonnet,claude-haiku,gpt-4,gpt-3.5-turbo',
            ],
            'interaction_type' => [
                'sometimes',
                'string',
                'in:SMART_SWITCH,LINEAR',
            ],
            'welcome_message' => ['nullable', 'string', 'max:1000'],
            'bubble_message' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'string', 'url'],
            'logo' => ['nullable', 'string', 'url'],
            'channels' => ['sometimes', 'array'],
            'channels.*' => ['string', 'in:telegram,whatsapp,messenger,voice,external'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A chatbot with this name already exists in your workspace.',
            'ai_model.in' => 'The selected AI model is not available.',
            'channels.*.in' => 'One or more selected channels are not available.',
        ];
    }
}

class UpdateChatbotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-chatbots');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $chatbotId = $this->route('chatbot')->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                "unique:ext_chatbots,name,{$chatbotId}",
            ],
            'description' => ['sometimes', 'string', 'max:1000'],
            'ai_model' => [
                'sometimes',
                'string',
                'in:claude-opus,claude-sonnet,claude-haiku,gpt-4,gpt-3.5-turbo',
            ],
            'interaction_type' => [
                'sometimes',
                'string',
                'in:SMART_SWITCH,LINEAR',
            ],
            'welcome_message' => ['sometimes', 'string', 'max:1000'],
            'bubble_message' => ['sometimes', 'string', 'max:500'],
            'avatar' => ['sometimes', 'nullable', 'string', 'url'],
            'logo' => ['sometimes', 'nullable', 'string', 'url'],
            'channels' => ['sometimes', 'array'],
            'channels.*' => ['string', 'in:telegram,whatsapp,messenger,voice,external'],
        ];
    }
}

class StoreConversationMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $conversation = $this->route('conversation');
        
        // Check policy
        return $this->user()->can('view', $conversation);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:50000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Please enter a message.',
            'message.max' => 'Message cannot exceed 5000 characters.',
            'attachment.max' => 'File size cannot exceed 50MB.',
        ];
    }
}

class TransferConversationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('transfer', $this->route('conversation'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'agent_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'agent_id.required' => 'Please select an agent to transfer to.',
            'agent_id.exists' => 'The selected agent does not exist.',
        ];
    }
}

class CloseConversationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('close', $this->route('conversation'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

class RateFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('rate', $this->route('conversation'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Please provide a rating.',
            'rating.min' => 'Rating must be between 1 and 5.',
            'rating.max' => 'Rating must be between 1 and 5.',
        ];
    }
}
