## 1. Order Confirmed

```html
<div
  style="max-width: 600px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #ffffff;"
>
  <div
    style="background: linear-gradient(135deg, #2271b1 0%, #135e96 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;"
  >
    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">
      Order Confirmed!
    </h1>
    <p
      style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px; opacity: 0.95;"
    >
      Thank you for your order
    </p>
  </div>

  <div style="padding: 30px; background-color: #ffffff;">
    <p
      style="color: #1d2327; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;"
    >
      Dear {{customer_name}},
    </p>
    <p
      style="color: #646970; font-size: 15px; line-height: 1.6; margin: 0 0 30px 0;"
    >
      We're excited to confirm that your order
      <strong style="color: #2271b1;">#{{order_number}}</strong> has been
      received and confirmed. We're preparing your items with care!
    </p>

    <div
      style="background: #f6f7f7; border-left: 4px solid #2271b1; padding: 20px; margin: 25px 0; border-radius: 4px;"
    >
      <h3
        style="color: #1d2327; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;"
      >
        Order Information
      </h3>
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Order Number:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #1d2327; font-size: 14px; font-weight: 600;"
          >
            #{{order_number}}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Order Date:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #1d2327; font-size: 14px;"
          >
            {{order_date}}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Status:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #2271b1; font-size: 14px; font-weight: 600;"
          >
            {{order_status}}
          </td>
        </tr>
      </table>
    </div>

    <h3
      style="color: #1d2327; margin: 30px 0 15px 0; font-size: 18px; font-weight: 600; border-bottom: 2px solid #f0f0f1; padding-bottom: 10px;"
    >
      Order Items
    </h3>
    {{order_items}}

    <div
      style="background: #f6f7f7; padding: 20px; margin: 25px 0; border-radius: 6px;"
    >
      <h3
        style="color: #1d2327; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;"
      >
        Order Summary
      </h3>
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td
            style="padding: 10px 0; color: #646970; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            Subtotal:
          </td>
          <td
            style="padding: 10px 0; text-align: right; color: #1d2327; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            {{order_subtotal}}
          </td>
        </tr>
        <tr>
          <td
            style="padding: 10px 0; color: #646970; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            Shipping:
          </td>
          <td
            style="padding: 10px 0; text-align: right; color: #1d2327; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            {{order_shipping}}
          </td>
        </tr>
        <tr>
          <td
            style="padding: 10px 0; color: #646970; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            Tax:
          </td>
          <td
            style="padding: 10px 0; text-align: right; color: #1d2327; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            {{order_tax}}
          </td>
        </tr>
        <tr>
          <td
            style="padding: 15px 0 10px 0; color: #1d2327; font-size: 16px; font-weight: 600;"
          >
            Total:
          </td>
          <td
            style="padding: 15px 0 10px 0; text-align: right; color: #2271b1; font-size: 18px; font-weight: 700;"
          >
            {{order_total}}
          </td>
        </tr>
      </table>
    </div>

    <div
      style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 25px 0;"
    >
      <div>
        <h3
          style="color: #1d2327; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;"
        >
          Billing Address
        </h3>
        <p
          style="color: #646970; font-size: 14px; line-height: 1.6; margin: 0;"
        >
          {{billing_address}}
        </p>
      </div>
      <div>
        <h3
          style="color: #1d2327; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;"
        >
          Shipping Address
        </h3>
        <p
          style="color: #646970; font-size: 14px; line-height: 1.6; margin: 0;"
        >
          {{shipping_address}}
        </p>
      </div>
    </div>

    <div
      style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin: 25px 0; border-radius: 4px;"
    >
      <p style="color: #1d2327; font-size: 14px; line-height: 1.6; margin: 0;">
        <strong>Payment Method:</strong> {{payment_method}}
      </p>
      {{#if transaction_id}}
      <p style="color: #646970; font-size: 13px; margin: 5px 0 0 0;">
        <strong>Transaction ID:</strong> {{transaction_id}}
      </p>
      {{/if}}
    </div>

    <p
      style="color: #646970; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0;"
    >
      We'll keep you updated on your order status. You'll receive notifications
      as your order progresses.
    </p>
    <p
      style="color: #1d2327; font-size: 15px; line-height: 1.6; margin: 15px 0 0 0; font-weight: 500;"
    >
      Thank you for choosing us!
    </p>
  </div>

  <div
    style="background: #f6f7f7; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #dcdcde;"
  >
    <p style="color: #646970; font-size: 12px; margin: 0;">
      If you have any questions, please contact our support team.
    </p>
  </div>
</div>
```

## 2. Order Proceeded

```html
<div
  style="max-width: 600px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #ffffff;"
>
  <div
    style="background: linear-gradient(135deg, #00a32a 0%, #007a20 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;"
  >
    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">
      Order Being Processed
    </h1>
    <p
      style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px; opacity: 0.95;"
    >
      We're working on your order
    </p>
  </div>

  <div style="padding: 30px; background-color: #ffffff;">
    <p
      style="color: #1d2327; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;"
    >
      Dear {{customer_name}},
    </p>
    <p
      style="color: #646970; font-size: 15px; line-height: 1.6; margin: 0 0 30px 0;"
    >
      Great news! Your order
      <strong style="color: #00a32a;">#{{order_number}}</strong> is now being
      processed. Our team is carefully preparing your items for shipment.
    </p>

    <div
      style="background: #f0f6fc; border-left: 4px solid #00a32a; padding: 20px; margin: 25px 0; border-radius: 4px;"
    >
      <h3
        style="color: #1d2327; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;"
      >
        Order Information
      </h3>
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Order Number:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #1d2327; font-size: 14px; font-weight: 600;"
          >
            #{{order_number}}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Order Date:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #1d2327; font-size: 14px;"
          >
            {{order_date}}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Status:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #00a32a; font-size: 14px; font-weight: 600;"
          >
            {{order_status}}
          </td>
        </tr>
      </table>
    </div>

    <h3
      style="color: #1d2327; margin: 30px 0 15px 0; font-size: 18px; font-weight: 600; border-bottom: 2px solid #f0f0f1; padding-bottom: 10px;"
    >
      Order Items
    </h3>
    {{order_items}}

    <div
      style="background: #f6f7f7; padding: 20px; margin: 25px 0; border-radius: 6px; text-align: center;"
    >
      <div
        style="display: inline-block; background: #00a32a; color: #ffffff; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; margin-bottom: 15px;"
      >
        ✓ Processing in Progress
      </div>
      <p style="color: #646970; font-size: 14px; line-height: 1.6; margin: 0;">
        We are working diligently on your order and will notify you once it's
        ready for delivery.
      </p>
    </div>

    <div
      style="background: #fff3cd; border-left: 4px solid #ffb900; padding: 15px; margin: 25px 0; border-radius: 4px;"
    >
      <p style="color: #1d2327; font-size: 14px; line-height: 1.6; margin: 0;">
        <strong>What's Next?</strong>
      </p>
      <p
        style="color: #646970; font-size: 13px; line-height: 1.6; margin: 8px 0 0 0;"
      >
        Once your order is processed and ready, we'll send it out for delivery.
        You'll receive another notification when your order is on its way!
      </p>
    </div>

    <p
      style="color: #646970; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0;"
    >
      Thank you for your patience. We appreciate your business!
    </p>
  </div>

  <div
    style="background: #f6f7f7; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #dcdcde;"
  >
    <p style="color: #646970; font-size: 12px; margin: 0;">
      Questions about your order? Contact our support team anytime.
    </p>
  </div>
</div>
```

## 3. Out For Delivery

```html
<div
  style="max-width: 600px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #ffffff;"
>
  <div
    style="background: linear-gradient(135deg, #d63638 0%, #b32d2e 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;"
  >
    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">
      🚚 Out for Delivery!
    </h1>
    <p
      style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px; opacity: 0.95;"
    >
      Your order is on its way
    </p>
  </div>

  <div style="padding: 30px; background-color: #ffffff;">
    <p
      style="color: #1d2327; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;"
    >
      Dear {{customer_name}},
    </p>
    <p
      style="color: #646970; font-size: 15px; line-height: 1.6; margin: 0 0 30px 0;"
    >
      Great news! Your order
      <strong style="color: #d63638;">#{{order_number}}</strong> is out for
      delivery and should arrive at your location shortly.
    </p>

    <div
      style="background: #f6f7f7; border-left: 4px solid #d63638; padding: 20px; margin: 25px 0; border-radius: 4px;"
    >
      <h3
        style="color: #1d2327; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;"
      >
        Order Information
      </h3>
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Order Number:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #1d2327; font-size: 14px; font-weight: 600;"
          >
            #{{order_number}}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Order Date:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #1d2327; font-size: 14px;"
          >
            {{order_date}}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Status:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #d63638; font-size: 14px; font-weight: 600;"
          >
            {{order_status}}
          </td>
        </tr>
      </table>
    </div>

    <h3
      style="color: #1d2327; margin: 30px 0 15px 0; font-size: 18px; font-weight: 600; border-bottom: 2px solid #f0f0f1; padding-bottom: 10px;"
    >
      Order Items
    </h3>
    {{order_items}}

    <div
      style="background: #fff3cd; border-left: 4px solid #ffb900; padding: 20px; margin: 25px 0; border-radius: 4px;"
    >
      <h3
        style="color: #1d2327; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;"
      >
        📍 Delivery Address
      </h3>
      <p style="color: #646970; font-size: 14px; line-height: 1.8; margin: 0;">
        {{shipping_address}}
      </p>
    </div>

    <div
      style="background: #d1ecf1; border-left: 4px solid #2271b1; padding: 20px; margin: 25px 0; border-radius: 4px;"
    >
      <p
        style="color: #1d2327; font-size: 15px; line-height: 1.6; margin: 0 0 10px 0; font-weight: 600;"
      >
        ⚠️ Important Delivery Information
      </p>
      <ul
        style="color: #646970; font-size: 14px; line-height: 1.8; margin: 0; padding-left: 20px;"
      >
        <li>Please ensure someone is available to receive the delivery</li>
        <li>Have your ID ready if required for verification</li>
        <li>Check the delivery address above is correct</li>
      </ul>
    </div>

    <div
      style="background: #f6f7f7; padding: 20px; margin: 25px 0; border-radius: 6px; text-align: center;"
    >
      <div
        style="display: inline-block; background: #d63638; color: #ffffff; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600;"
      >
        🚚 On the Way
      </div>
      <p
        style="color: #646970; font-size: 14px; line-height: 1.6; margin: 15px 0 0 0;"
      >
        Your order should arrive soon. We'll notify you once it's been
        delivered!
      </p>
    </div>

    <p
      style="color: #646970; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0;"
    >
      Thank you for your order. We hope you enjoy your purchase!
    </p>
  </div>

  <div
    style="background: #f6f7f7; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #dcdcde;"
  >
    <p style="color: #646970; font-size: 12px; margin: 0;">
      Need to update your delivery address? Contact us immediately.
    </p>
  </div>
</div>
```

## 4. Order Completed

```html
<div
  style="max-width: 600px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #ffffff;"
>
  <div
    style="background: linear-gradient(135deg, #00a32a 0%, #007a20 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;"
  >
    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 600;">
      ✓ Order Completed!
    </h1>
    <p
      style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px; opacity: 0.95;"
    >
      Thank you for your purchase
    </p>
  </div>

  <div style="padding: 30px; background-color: #ffffff;">
    <p
      style="color: #1d2327; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;"
    >
      Dear {{customer_name}},
    </p>
    <p
      style="color: #646970; font-size: 15px; line-height: 1.6; margin: 0 0 30px 0;"
    >
      We're delighted to inform you that your order
      <strong style="color: #00a32a;">#{{order_number}}</strong> has been
      successfully completed and delivered. We hope you're happy with your
      purchase!
    </p>

    <div
      style="background: #f0f6fc; border-left: 4px solid #00a32a; padding: 20px; margin: 25px 0; border-radius: 4px;"
    >
      <h3
        style="color: #1d2327; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;"
      >
        Order Information
      </h3>
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Order Number:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #1d2327; font-size: 14px; font-weight: 600;"
          >
            #{{order_number}}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Order Date:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #1d2327; font-size: 14px;"
          >
            {{order_date}}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #646970; font-size: 14px;">
            <strong style="color: #1d2327;">Status:</strong>
          </td>
          <td
            style="padding: 8px 0; text-align: right; color: #00a32a; font-size: 14px; font-weight: 600;"
          >
            {{order_status}}
          </td>
        </tr>
      </table>
    </div>

    <h3
      style="color: #1d2327; margin: 30px 0 15px 0; font-size: 18px; font-weight: 600; border-bottom: 2px solid #f0f0f1; padding-bottom: 10px;"
    >
      Order Items
    </h3>
    {{order_items}}

    <div
      style="background: #f6f7f7; padding: 20px; margin: 25px 0; border-radius: 6px;"
    >
      <h3
        style="color: #1d2327; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;"
      >
        Order Summary
      </h3>
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td
            style="padding: 10px 0; color: #646970; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            Subtotal:
          </td>
          <td
            style="padding: 10px 0; text-align: right; color: #1d2327; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            {{order_subtotal}}
          </td>
        </tr>
        <tr>
          <td
            style="padding: 10px 0; color: #646970; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            Shipping:
          </td>
          <td
            style="padding: 10px 0; text-align: right; color: #1d2327; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            {{order_shipping}}
          </td>
        </tr>
        <tr>
          <td
            style="padding: 10px 0; color: #646970; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            Tax:
          </td>
          <td
            style="padding: 10px 0; text-align: right; color: #1d2327; font-size: 14px; border-bottom: 1px solid #dcdcde;"
          >
            {{order_tax}}
          </td>
        </tr>
        <tr>
          <td
            style="padding: 15px 0 10px 0; color: #1d2327; font-size: 16px; font-weight: 600;"
          >
            Total:
          </td>
          <td
            style="padding: 15px 0 10px 0; text-align: right; color: #00a32a; font-size: 18px; font-weight: 700;"
          >
            {{order_total}}
          </td>
        </tr>
      </table>
    </div>

    <div
      style="background: #d4edda; border-left: 4px solid #00a32a; padding: 20px; margin: 25px 0; border-radius: 4px; text-align: center;"
    >
      <div
        style="display: inline-block; background: #00a32a; color: #ffffff; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; margin-bottom: 15px;"
      >
        ✓ Successfully Delivered
      </div>
      <p
        style="color: #1d2327; font-size: 15px; line-height: 1.6; margin: 0; font-weight: 500;"
      >
        Your order has been completed and delivered!
      </p>
    </div>

    <div
      style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 20px; margin: 25px 0; border-radius: 4px;"
    >
      <h3
        style="color: #1d2327; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;"
      >
        Delivery Address
      </h3>
      <p style="color: #646970; font-size: 14px; line-height: 1.8; margin: 0;">
        {{shipping_address}}
      </p>
    </div>

    <div
      style="background: #fff3cd; border-left: 4px solid #ffb900; padding: 15px; margin: 25px 0; border-radius: 4px;"
    >
      <p style="color: #1d2327; font-size: 14px; line-height: 1.6; margin: 0;">
        <strong>We'd Love Your Feedback!</strong>
      </p>
      <p
        style="color: #646970; font-size: 13px; line-height: 1.6; margin: 8px 0 0 0;"
      >
        Your opinion matters to us. If you have a moment, we'd appreciate
        hearing about your experience with this order.
      </p>
    </div>

    <p
      style="color: #646970; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0;"
    >
      We hope you enjoy your purchase and look forward to serving you again in
      the future!
    </p>
    <p
      style="color: #1d2327; font-size: 15px; line-height: 1.6; margin: 15px 0 0 0; font-weight: 500;"
    >
      Thank you for your business!
    </p>
  </div>

  <div
    style="background: #f6f7f7; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #dcdcde;"
  >
    <p style="color: #646970; font-size: 12px; margin: 0;">
      Need help with your order? Our support team is here to assist you.
    </p>
  </div>
</div>
```
