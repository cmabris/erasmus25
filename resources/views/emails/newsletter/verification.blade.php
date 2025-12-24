<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('common.newsletter.verify_subscription') }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Main Container -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #003399 0%, #0066CC 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                {{ __('common.newsletter.email_welcome') }}
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                {{ __('common.newsletter.email_hello') }}@if($subscription->name) {{ $subscription->name }}@endif,
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                {{ __('common.newsletter.email_thanks') }}
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                {{ __('common.newsletter.email_complete_subscription') }}
                            </p>
                            
                            <!-- Verification Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="padding: 0 0 30px 0;">
                                        <a href="{{ $verificationUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #003399; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                                            {{ __('common.newsletter.email_verify_button') }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Alternative Link -->
                            <p style="margin: 0 0 30px 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                {{ __('common.newsletter.email_button_not_working') }}<br>
                                <a href="{{ $verificationUrl }}" style="color: #003399; word-break: break-all;">{{ $verificationUrl }}</a>
                            </p>
                            
                            <!-- Programs Info -->
                            @if($subscription->programs && count($subscription->programs) > 0)
                                <div style="background-color: #f9fafb; border-left: 4px solid #003399; padding: 16px; margin: 30px 0; border-radius: 4px;">
                                    <p style="margin: 0 0 8px 0; color: #374151; font-size: 14px; font-weight: 600;">
                                        {{ __('common.newsletter.programs_interest_label') }}
                                    </p>
                                    <p style="margin: 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                        {{ implode(', ', $subscription->programs) }}
                                    </p>
                                </div>
                            @endif
                            
                            <!-- What to Expect -->
                            <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; margin: 30px 0; border-radius: 4px;">
                                <p style="margin: 0 0 12px 0; color: #1e40af; font-size: 14px; font-weight: 600;">
                                    {{ __('common.newsletter.what_will_receive') }}
                                </p>
                                <ul style="margin: 0; padding-left: 20px; color: #1e3a8a; font-size: 14px; line-height: 1.8;">
                                    <li>{{ __('common.newsletter.receive_calls') }}</li>
                                    <li>{{ __('common.newsletter.receive_news') }}</li>
                                    <li>{{ __('common.newsletter.receive_events') }}</li>
                                    <li>{{ __('common.newsletter.receive_resolutions') }}</li>
                                </ul>
                            </div>
                            
                            <!-- Unsubscribe Info -->
                            <p style="margin: 30px 0 0 0; color: #6b7280; font-size: 14px; line-height: 1.6; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                                {{ __('common.newsletter.email_unsubscribe_info') }}<br>
                                <a href="{{ $unsubscribeUrl }}" style="color: #dc2626; text-decoration: underline;">{{ __('common.newsletter.cancel_subscription') }}</a>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">
                                <strong>Erasmus+ Centro (Murcia)</strong>
                            </p>
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                {{ __('common.newsletter.email_auto') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

