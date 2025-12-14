/**
 * Customer Order Tracking JavaScript
 *
 * @package PExpress
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function () {
        initSearchAndFilter();
        initHeartbeat();
    });

    /**
     * Initialize search and filter functionality
     */
    function initSearchAndFilter() {
        var $searchInput = $('#polar-order-search');
        var $statusFilter = $('#polar-status-filter');
        var $ordersList = $('#polar-tracking-orders-list');
        var $orderCards = $ordersList.find('.polar-order-card');

        function filterOrders() {
            var searchValue = $searchInput.val().toLowerCase().trim();
            var statusValue = $statusFilter.val();
            var visibleCount = 0;

            $orderCards.each(function () {
                var $card = $(this);
                var searchText = $card.data('search-text') || '';
                var cardStatus = $card.data('status') || '';

                var searchMatch = !searchValue || searchText.indexOf(searchValue) !== -1;
                var statusMatch = !statusValue || statusValue === 'all' || cardStatus === statusValue;

                if (searchMatch && statusMatch) {
                    $card.fadeIn(200);
                    visibleCount++;
                } else {
                    $card.fadeOut(200);
                }
            });

            // Show empty state if no orders visible
            var $emptyState = $ordersList.find('.polar-empty-state');
            if (visibleCount === 0 && $orderCards.length > 0) {
                if ($emptyState.length === 0) {
                    $ordersList.append(
                        '<div class="polar-empty-state">' +
                        '<div class="empty-state-icon">' +
                        '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                        '<path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                        '</svg>' +
                        '</div>' +
                        '<h3>No orders found</h3>' +
                        '<p>Try adjusting your search or filter.</p>' +
                        '</div>'
                    );
                }
                $emptyState.fadeIn(200);
            } else {
                $emptyState.fadeOut(200);
            }
        }

        // Bind search input event
        $searchInput.on('input', function () {
            clearTimeout($searchInput.data('timeout'));
            var timeout = setTimeout(filterOrders, 300);
            $searchInput.data('timeout', timeout);
        });

        // Bind status filter change event
        $statusFilter.on('change', filterOrders);
    }

    /**
     * Initialize WordPress Heartbeat for real-time updates
     */
    function initHeartbeat() {
        // Ensure heartbeat is enabled
        if (typeof wp === 'undefined' || typeof wp.heartbeat === 'undefined') {
            return;
        }

        // Listen for heartbeat tick
        $(document).on('heartbeat-tick', function (e, data) {
            if (data.polar_order_tracking) {
                updateOrderTracking(data.polar_order_tracking);
            }
        });

        // Request order tracking data for visible orders
        $(document).on('heartbeat-send', function (e, data) {
            var orderIds = [];
            $('.polar-order-card').each(function () {
                var orderId = $(this).data('order-id');
                if (orderId) {
                    orderIds.push(orderId);
                }
            });

            if (orderIds.length > 0) {
                data.polar_order_tracking = {
                    order_ids: orderIds
                };
            }
        });
    }

    /**
     * Update order tracking with new data
     */
    function updateOrderTracking(trackingDataArray) {
        if (!trackingDataArray || !Array.isArray(trackingDataArray)) {
            return;
        }

        trackingDataArray.forEach(function (data) {
            if (!data || !data.statuses || !data.order_id) {
                return;
            }

            var orderId = data.order_id;
            var $orderCard = $('.polar-order-card[data-order-id="' + orderId + '"]');

            if (!$orderCard.length) {
                return;
            }

            var statuses = data.statuses;

            // Update delivery status (primary status)
            if (statuses.delivery && statuses.delivery.status) {
                var deliveryStatus = statuses.delivery.status;
                var overallStatus = 'pending';

                if (deliveryStatus === 'customer_served') {
                    overallStatus = 'completed';
                } else if (['meet_point_arrived', 'delivery_location_arrived', 'service_in_progress', 'service_complete'].indexOf(deliveryStatus) !== -1) {
                    overallStatus = 'in_progress';
                }

                // Update overall status badge
                var $statusBadge = $orderCard.find('.polar-order-card-status .polar-status-badge');
                $statusBadge.removeClass('status-pending status-in_progress status-completed')
                    .addClass('status-' + overallStatus);

                var statusText = '';
                if (overallStatus === 'completed') {
                    statusText = 'Completed';
                } else if (overallStatus === 'in_progress') {
                    statusText = 'In Progress';
                } else {
                    statusText = 'Pending';
                }
                $statusBadge.text(statusText);

                // Update data attribute
                $orderCard.attr('data-status', overallStatus);

                // Update delivery status mini badge
                var $deliveryBadge = $orderCard.find('.polar-status-mini-item:has(.status-mini-label:contains("Delivery")) .status-mini-badge');
                var deliveryStatusClass = deliveryStatus === 'customer_served' ? 'completed' : (deliveryStatus === 'pending' ? 'pending' : 'in-progress');
                $deliveryBadge.removeClass('status-pending status-in-progress status-completed')
                    .addClass('status-' + deliveryStatusClass);

                // Update status label
                var statusLabels = {
                    'pending': 'Pending',
                    'meet_point_arrived': 'Reached Meet Point',
                    'delivery_location_arrived': 'Reached Delivery Location',
                    'service_in_progress': 'Service In Progress',
                    'service_complete': 'Service Completed',
                    'customer_served': 'Ice-cream Delivered'
                };
                $deliveryBadge.text(statusLabels[deliveryStatus] || deliveryStatus);
            }

            // Update other role statuses
            if (statuses.fridge && statuses.fridge.status) {
                var fridgeStatus = statuses.fridge.status;
                var $fridgeBadge = $orderCard.find('.polar-status-mini-item:has(.status-mini-label:contains("Fridge")) .status-mini-badge');
                var fridgeStatusClass = fridgeStatus === 'fridge_returned' ? 'completed' : (fridgeStatus === 'pending' ? 'pending' : 'in-progress');
                $fridgeBadge.removeClass('status-pending status-in-progress status-completed')
                    .addClass('status-' + fridgeStatusClass);

                var fridgeLabels = {
                    'pending': 'Pending',
                    'fridge_drop': 'Fridge Delivered On-site',
                    'fridge_collected': 'Fridge Collected On-site',
                    'fridge_returned': 'Fridge Returned to Base'
                };
                $fridgeBadge.text(fridgeLabels[fridgeStatus] || fridgeStatus);
            }

            if (statuses.distributor && statuses.distributor.status) {
                var distributorStatus = statuses.distributor.status;
                var $distributorBadge = $orderCard.find('.polar-status-mini-item:has(.status-mini-label:contains("Product Provider")) .status-mini-badge');
                var distributorStatusClass = distributorStatus === 'handoff_complete' ? 'completed' : (distributorStatus === 'pending' ? 'pending' : 'in-progress');
                $distributorBadge.removeClass('status-pending status-in-progress status-completed')
                    .addClass('status-' + distributorStatusClass);

                var distributorLabels = {
                    'pending': 'Pending',
                    'distributor_prep': 'Product Provider Preparing',
                    'out_for_delivery': 'Out for Delivery',
                    'handoff_complete': 'Product Provider Handoff Complete'
                };
                $distributorBadge.text(distributorLabels[distributorStatus] || distributorStatus);
            }

            if (statuses.hr && statuses.hr.status) {
                var hrStatus = statuses.hr.status;
                var $hrBadge = $orderCard.find('.polar-status-mini-item:has(.status-mini-label:contains("Agency")) .status-mini-badge');
                var hrStatusClass = (hrStatus === 'assigned' || hrStatus === 'proceeded') ? 'in-progress' : 'pending';
                $hrBadge.removeClass('status-pending status-in-progress status-completed')
                    .addClass('status-' + hrStatusClass);

                var hrLabels = {
                    'pending': 'Pending',
                    'assigned': 'Assigned',
                    'proceeded': 'Proceeded'
                };
                $hrBadge.text(hrLabels[hrStatus] || hrStatus);
            }

            // Add visual indicator for update
            $orderCard.addClass('polar-updated');
            setTimeout(function () {
                $orderCard.removeClass('polar-updated');
            }, 1000);
        });
    }

})(jQuery);

