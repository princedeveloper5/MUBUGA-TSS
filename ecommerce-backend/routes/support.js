const express = require('express');
const SupportTicket = require('../models/SupportTicket');
const Order = require('../models/Order');
const Product = require('../models/Product');
const NotificationService = require('../utils/notifications');
const multer = require('multer');
const path = require('path');
const router = express.Router();

// File upload configuration
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'uploads/support/');
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, file.fieldname + '-' + uniqueSuffix + path.extname(file.originalname));
  }
});

const upload = multer({ 
  storage: storage,
  limits: { fileSize: 5 * 1024 * 1024 }, // 5MB limit
  fileFilter: (req, file, cb) => {
    const allowedTypes = /jpeg|jpg|png|gif|pdf|doc|docx/;
    const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
    const mimetype = allowedTypes.test(file.mimetype);

    if (mimetype && extname) {
      return cb(null, true);
    } else {
      cb(new Error('Invalid file type. Only images and documents are allowed.'));
    }
  }
});

// Create support ticket
router.post('/tickets', upload.array('attachments', 5), async (req, res) => {
  try {
    const { customerName, customerEmail, customerPhone, subject, category, priority, description } = req.body;
    
    // Validation
    if (!customerName || !customerEmail || !customerPhone || !subject || !category || !description) {
      return res.status(400).json({
        success: false,
        message: 'All required fields must be provided'
      });
    }

    // Create ticket
    const ticket = new SupportTicket({
      customerName,
      customerEmail,
      customerPhone,
      subject,
      category,
      priority: priority || 'medium',
      description,
      attachments: req.files ? req.files.map(file => ({
        filename: file.filename,
        originalName: file.originalname,
        size: file.size,
        mimeType: file.mimetype,
        url: `/uploads/support/${file.filename}`
      })) : []
    });

    await ticket.save();

    // Send confirmation email
    await NotificationService.sendEmail(
      customerEmail,
      'Support Ticket Created - RwandaShop',
      `Your support ticket ${ticket.ticketId} has been created. We'll respond within 24 hours.`
    );

    res.status(201).json({
      success: true,
      message: 'Support ticket created successfully',
      data: {
        ticketId: ticket.ticketId,
        status: ticket.status,
        createdAt: ticket.createdAt
      }
    });
  } catch (error) {
    console.error('Support ticket creation error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create support ticket'
    });
  }
});

// Get customer tickets
router.get('/tickets/customer/:customerEmail', async (req, res) => {
  try {
    const { customerEmail } = req.params;
    const { page = 1, limit = 10, status } = req.query;
    
    // Build query
    const query = { customerEmail: customerEmail.toLowerCase() };
    if (status) {
      query.status = status;
    }
    
    const tickets = await SupportTicket.find(query)
      .sort({ createdAt: -1 })
      .limit(limit * 1)
      .skip((page - 1) * limit)
      .populate('orderId productId', 'name image')
      .select('-responses')
      .lean();
    
    const total = await SupportTicket.countDocuments(query);
    
    res.json({
      success: true,
      data: tickets,
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total,
        pages: Math.ceil(total / limit)
      }
    });
  } catch (error) {
    console.error('Error fetching customer tickets:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch tickets'
    });
  }
});

// Get all tickets (Admin)
router.get('/tickets', async (req, res) => {
  try {
    const { page = 1, limit = 20, status, category, priority, assignedTo } = req.query;
    
    // Build query
    const query = {};
    if (status) query.status = status;
    if (category) query.category = category;
    if (priority) query.priority = priority;
    if (assignedTo) query.assignedTo = assignedTo;
    
    const tickets = await SupportTicket.find(query)
      .sort({ priority: -1, createdAt: -1 })
      .limit(limit * 1)
      .skip((page - 1) * limit)
      .populate('orderId productId', 'name image price')
      .lean();
    
    const total = await SupportTicket.countDocuments(query);
    
    res.json({
      success: true,
      data: tickets,
      pagination: {
        page: parseInt(page),
        limit: parseInt(limit),
        total,
        pages: Math.ceil(total / limit)
      }
    });
  } catch (error) {
    console.error('Error fetching tickets:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch tickets'
    });
  }
});

// Get single ticket
router.get('/tickets/:ticketId', async (req, res) => {
  try {
    const { ticketId } = req.params;
    
    const ticket = await SupportTicket.findOne({ ticketId })
      .populate('orderId productId', 'name image price')
      .lean();
    
    if (!ticket) {
      return res.status(404).json({
        success: false,
        message: 'Ticket not found'
      });
    }
    
    res.json({
      success: true,
      data: ticket
    });
  } catch (error) {
    console.error('Error fetching ticket:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch ticket'
    });
  }
});

// Add response to ticket
router.post('/tickets/:ticketId/respond', upload.array('attachments', 3), async (req, res) => {
  try {
    const { ticketId } = req.params;
    const { message, respondedBy, isInternal = false } = req.body;
    
    if (!message || !respondedBy) {
      return res.status(400).json({
        success: false,
        message: 'Message and responder name are required'
      });
    }
    
    const ticket = await SupportTicket.findOne({ ticketId });
    if (!ticket) {
      return res.status(404).json({
        success: false,
        message: 'Ticket not found'
      });
    }
    
    // Add response
    const response = {
      message,
      respondedBy,
      isInternal,
      respondedAt: new Date(),
      attachments: req.files ? req.files.map(file => ({
        filename: file.filename,
        url: `/uploads/support/${file.filename}`
      })) : []
    };
    
    ticket.responses.push(response);
    
    // Update ticket status if not internal
    if (!isInternal) {
      ticket.status = 'awaiting_customer';
    }
    
    await ticket.save();
    
    // Send email notification to customer
    if (!isInternal) {
      await NotificationService.sendEmail(
        ticket.customerEmail,
        `Response to Support Ticket ${ticketId} - RwandaShop`,
        `Your support ticket ${ticketId} has received a response. Please check your ticket for details.`
      );
    }
    
    res.json({
      success: true,
      message: 'Response added successfully',
      data: response
    });
  } catch (error) {
    console.error('Error adding response:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to add response'
    });
  }
});

// Update ticket status
router.put('/tickets/:ticketId/status', async (req, res) => {
  try {
    const { ticketId } = req.params;
    const { status, assignedTo } = req.body;
    
    const ticket = await SupportTicket.findOne({ ticketId });
    if (!ticket) {
      return res.status(404).json({
        success: false,
        message: 'Ticket not found'
      });
    }
    
    // Update status
    if (status) ticket.status = status;
    if (assignedTo) ticket.assignedTo = assignedTo;
    
    // Set resolved timestamp
    if (status === 'resolved') {
      ticket.resolvedAt = new Date();
    }
    
    await ticket.save();
    
    // Send notification if resolved
    if (status === 'resolved') {
      await NotificationService.sendEmail(
        ticket.customerEmail,
        `Support Ticket ${ticketId} Resolved - RwandaShop`,
        `Your support ticket ${ticketId} has been resolved. Thank you for contacting us!`
      );
    }
    
    res.json({
      success: true,
      message: 'Ticket updated successfully',
      data: ticket
    });
  } catch (error) {
    console.error('Error updating ticket:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update ticket'
    });
  }
});

// Add customer satisfaction rating
router.post('/tickets/:ticketId/satisfaction', async (req, res) => {
  try {
    const { ticketId } = req.params;
    const { rating, feedback } = req.body;
    
    if (!rating || rating < 1 || rating > 5) {
      return res.status(400).json({
        success: false,
        message: 'Rating must be between 1 and 5'
      });
    }
    
    const ticket = await SupportTicket.findOne({ ticketId });
    if (!ticket) {
      return res.status(404).json({
        success: false,
        message: 'Ticket not found'
      });
    }
    
    ticket.satisfaction = {
      rating: parseInt(rating),
      feedback: feedback || ''
    };
    
    await ticket.save();
    
    res.json({
      success: true,
      message: 'Satisfaction rating recorded successfully'
    });
  } catch (error) {
    console.error('Error adding satisfaction rating:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to record satisfaction rating'
    });
  }
});

// Get support statistics
router.get('/stats', async (req, res) => {
  try {
    const { period = '30d' } = req.query;
    
    // Calculate date range
    const days = period === '1d' ? 1 : period === '7d' ? 7 : period === '30d' ? 30 : 7;
    const dateFilter = {
      $gte: new Date(Date.now() - days * 24 * 60 * 60 * 1000)
    };
    
    const [
      totalTickets,
      openTickets,
      resolvedTickets,
      ticketsByCategory,
      ticketsByPriority,
      averageResolutionTime
    ] = await Promise.all([
      SupportTicket.countDocuments({ createdAt: dateFilter }),
      SupportTicket.countDocuments({ status: 'open', createdAt: dateFilter }),
      SupportTicket.countDocuments({ status: 'resolved', createdAt: dateFilter }),
      SupportTicket.aggregate([
        { $match: { createdAt: dateFilter } },
        { $group: { _id: '$category', count: { $sum: 1 } } },
        { $sort: { count: -1 } }
      ]),
      SupportTicket.aggregate([
        { $match: { createdAt: dateFilter } },
        { $group: { _id: '$priority', count: { $sum: 1 } } },
        { $sort: { count: -1 } }
      ]),
      SupportTicket.aggregate([
        { $match: { status: 'resolved', createdAt: dateFilter } },
        {
          $group: {
            _id: null,
            avgResolutionTime: {
              $avg: { $subtract: ['$resolvedAt', '$createdAt'] }
            }
          }
        }
      ])
    ]);
    
    res.json({
      success: true,
      data: {
        totalTickets,
        openTickets,
        resolvedTickets,
        resolutionRate: totalTickets > 0 ? ((resolvedTickets / totalTickets) * 100).toFixed(2) : 0,
        ticketsByCategory,
        ticketsByPriority,
        averageResolutionTime: averageResolutionTime[0] ? Math.round(averageResolutionTime[0].avgResolutionTime / (1000 * 60 * 60)) : 0 // in hours
      }
    });
  } catch (error) {
    console.error('Error fetching support stats:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch support statistics'
    });
  }
});

module.exports = router;
