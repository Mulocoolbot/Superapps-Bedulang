import { router } from '@inertiajs/vue3'

/**
 * Service for Native Browser Notifications & Realtime Chime Audio
 */
export class NotificationService {
    /**
     * Request browser notification permission if supported
     */
    static async requestPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            try {
                await Notification.requestPermission()
            } catch (e) {
                console.warn('Could not request notification permission:', e)
            }
        }
    }

    /**
     * Play a clean, subtle audio chime sound using Web Audio API (no external asset required)
     */
    static playChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext
            if (!AudioContext) return

            const ctx = new AudioContext()
            const now = ctx.currentTime

            // First tone
            const osc1 = ctx.createOscillator()
            const gain1 = ctx.createGain()
            osc1.type = 'sine'
            osc1.frequency.setValueAtTime(523.25, now) // C5
            gain1.gain.setValueAtTime(0.15, now)
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3)
            osc1.connect(gain1)
            gain1.connect(ctx.destination)
            osc1.start(now)
            osc1.stop(now + 0.3)

            // Second tone (higher harmony)
            const osc2 = ctx.createOscillator()
            const gain2 = ctx.createGain()
            osc2.type = 'sine'
            osc2.frequency.setValueAtTime(659.25, now + 0.1) // E5
            gain2.gain.setValueAtTime(0.2, now + 0.1)
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5)
            osc2.connect(gain2)
            gain2.connect(ctx.destination)
            osc2.start(now + 0.1)
            osc2.stop(now + 0.5)
        } catch (e) {
            console.warn('Audio chime playback omitted:', e)
        }
    }

    /**
     * Show a native desktop pop-up notification on the user's OS/device
     */
    static showNativeNotification(title, message, targetUrl = null) {
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                const notification = new Notification(title || 'Notifikasi Aplikasi', {
                    body: message || 'Ada pembaruan pengaduan baru.',
                    icon: '/services/logobateng3.png',
                    tag: 'superapp-notification-' + Date.now(),
                })

                notification.onclick = () => {
                    window.focus()
                    if (targetUrl) {
                        router.visit(targetUrl)
                    }
                    notification.close()
                }
            } catch (e) {
                console.warn('Native notification failed:', e)
            }
        }
    }

    /**
     * Handle incoming real-time notifications: trigger audio chime & device pop-up for new items
     */
    static processNewNotifications(notifications, seenIdsSet) {
        let hasNewUnread = false

        notifications.forEach((item) => {
            if (!item.read_at && !seenIdsSet.has(item.id)) {
                seenIdsSet.add(item.id)
                hasNewUnread = true

                // Show native OS pop-up
                this.showNativeNotification(
                    item.data?.title || 'Notifikasi Pengaduan',
                    item.data?.message || 'Ada pembaruan data pengaduan.',
                    item.data?.url || null
                )
            }
        })

        if (hasNewUnread) {
            this.playChime()
        }
    }
}
