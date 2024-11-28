// Required packages
const express = require('express');
const axios = require('axios');
const bodyParser = require('body-parser');
const app = express();
app.use(bodyParser.json());

// Safaricom API credentials
const consumerKey = 'C7PpcimmZBQ5t5SG84zVyeGd08MxlBiAo6eYYiFotm8vyYtF';
const consumerSecret = 'UOWiPNFjnpf5G7bJezSfUxQpii1ASG2fHkVAhdVy66AGStcceMUlQ3bADGGq1AbB'; 

// Get access token function
async function getAccessToken() {
    const url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    const auth = Buffer.from(`${consumerKey}:${consumerSecret}`).toString('base64');
    
    try {
        const response = await axios.get(url, {
            headers: {
                Authorization: `Basic ${auth}`
            }
        });
        return response.data.access_token;
    } catch (error) {
        console.error('Error getting access token:', error);
        throw error;
    }
}

// Function to initiate STK Push
async function initiateSTKPush(phoneNumber, amount, orderId) {
    const accessToken = await getAccessToken();
    const url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    const data = {
        BusinessShortCode: '174379', // Use your Paybill or Till number here
        Password: Buffer.from('174379' + 'YOUR_LNM_PASSKEY' + new Date().toISOString().replace(/[-T:Z.]/g, '')).toString('base64'), // Replace with your LNM Passkey
        Timestamp: new Date().toISOString().replace(/[-T:Z.]/g, ''),
        TransactionType: 'CustomerPayBillOnline',
        Amount: amount,
        PartyA: phoneNumber, // Customer's phone number
        PartyB: '174379', // Your Paybill or Till number
        PhoneNumber: phoneNumber, // Customer's phone number
        CallBackURL: 'https://yourdomain.com/callback', // Your callback URL
        AccountReference: `order-${orderId}`, // Order ID or reference
        TransactionDesc: `Payment for order ${orderId}`
    };

    try {
        const response = await axios.post(url, data, {
            headers: {
                Authorization: `Bearer ${accessToken}`
            }
        });
        return response.data;
    } catch (error) {
        console.error('Error initiating STK push:', error);
        throw error;
    }
}

// Checkout endpoint
app.post('/checkout', async (req, res) => {
    const { phoneNumber, amount, orderId } = req.body;

    try {
        const result = await initiateSTKPush(phoneNumber, amount, orderId);
        res.json({
            message: 'Payment request sent. Please check your phone for M-Pesa prompt.',
            result
        });
    } catch (error) {
        res.status(500).json({
            message: 'Failed to initiate payment',
            error: error.toString()
        });
    }
});

// Callback endpoint to receive M-Pesa payment confirmation
app.post('/callback', (req, res) => {
    // Process the payment confirmation from Safaricom here
    console.log('Payment callback received:', req.body);
    res.json({ status: 'Success' });
});

// Start the server
app.listen(3000, () => {
    console.log('Server started on port 3000');
});