using Mahube_Pharmacy.Models;
using Microsoft.EntityFrameworkCore;

namespace Mahube_Pharmacy.Data;

public class AppDbContext : DbContext
{
    public AppDbContext(DbContextOptions<AppDbContext> options) : base(options) { }

    public DbSet<User> Users => Set<User>();
    public DbSet<Address> Addresses => Set<Address>();
    public DbSet<ProductCategory> ProductCategories => Set<ProductCategory>();
    public DbSet<Product> Products => Set<Product>();
    public DbSet<Cart> Carts => Set<Cart>();
    public DbSet<CartItem> CartItems => Set<CartItem>();
    public DbSet<Order> Orders => Set<Order>();
    public DbSet<OrderItem> OrderItems => Set<OrderItem>();
    public DbSet<PaymentLog> PaymentLogs => Set<PaymentLog>();
    public DbSet<Prescription> Prescriptions => Set<Prescription>();
    public DbSet<Consultation> Consultations => Set<Consultation>();

    protected override void OnModelCreating(ModelBuilder mb)
    {
        base.OnModelCreating(mb);

        // ==========================================
        // TABLE NAMES  (matches SHOW CREATE TABLE)
        // ==========================================
        mb.Entity<User>().ToTable("users");
        mb.Entity<Address>().ToTable("address");
        mb.Entity<ProductCategory>().ToTable("product_categories");
        mb.Entity<Product>().ToTable("products");
        mb.Entity<Cart>().ToTable("cart");
        mb.Entity<CartItem>().ToTable("cart_items");
        mb.Entity<Order>().ToTable("orders");
        mb.Entity<OrderItem>().ToTable("order_items");
        mb.Entity<PaymentLog>().ToTable("payment_logs");
        mb.Entity<Prescription>().ToTable("prescription");
        mb.Entity<Consultation>().ToTable("consultation");

        // ==========================================
        // PRIMARY KEYS  (only where convention misses)
        // ==========================================
        mb.Entity<ProductCategory>().HasKey(e => e.CategoryId);
        mb.Entity<CartItem>().HasKey(e => e.CartItemsId);
        mb.Entity<PaymentLog>().HasKey(e => e.LogId);
        // All other PKs match EF conventions (Id / <Type>Id) — no need to declare.

        // ==========================================
        // USERS
        // ==========================================
        mb.Entity<User>().Property(u => u.UserId).HasColumnName("user_id");
        mb.Entity<User>().Property(u => u.FullName).HasColumnName("fullname");
        mb.Entity<User>().Property(u => u.Email).HasColumnName("email");
        mb.Entity<User>().Property(u => u.PhoneNumber).HasColumnName("phone_number");
        mb.Entity<User>().Property(u => u.PasswordHash).HasColumnName("password_hash");
        mb.Entity<User>().Property(u => u.Password).HasColumnName("password");
        mb.Entity<User>().Property(u => u.Role).HasColumnName("role");

        // ==========================================
        // ADDRESS
        // ==========================================
        mb.Entity<Address>().Property(a => a.AddressId).HasColumnName("address_id");
        mb.Entity<Address>().Property(a => a.UserId).HasColumnName("user_id");
        mb.Entity<Address>().Property(a => a.AddressName).HasColumnName("address_name");
        mb.Entity<Address>().Property(a => a.StreetAddress).HasColumnName("street_address");
        mb.Entity<Address>().Property(a => a.City).HasColumnName("city");
        mb.Entity<Address>().Property(a => a.Province).HasColumnName("province");
        mb.Entity<Address>().Property(a => a.PostalCode).HasColumnName("postal_code");
        mb.Entity<Address>().Property(a => a.DeliveryInstruction).HasColumnName("delivery_instruction");

        // ==========================================
        // PRODUCT CATEGORIES
        // ==========================================
        mb.Entity<ProductCategory>().Property(c => c.CategoryId).HasColumnName("category_id");
        mb.Entity<ProductCategory>().Property(c => c.CategoryName).HasColumnName("category_name");
        // NOTE: DB has `description` column with no C# property — leave unmapped.

        // ==========================================
        // PRODUCTS
        // ==========================================
        mb.Entity<Product>().Property(p => p.ProductId).HasColumnName("product_id");
        mb.Entity<Product>().Property(p => p.CategoryId).HasColumnName("category_id");
        mb.Entity<Product>().Property(p => p.ProductName).HasColumnName("product_name");
        mb.Entity<Product>().Property(p => p.Description).HasColumnName("description");
        mb.Entity<Product>().Property(p => p.Price).HasColumnName("price").HasColumnType("float");
        mb.Entity<Product>().Property(p => p.StockQuantity).HasColumnName("stock_quantity");
        mb.Entity<Product>().Property(p => p.Image).HasColumnName("image");

        // ==========================================
        // CART   (single-row-per-product design; `id` is PK, no cart_id)
        // ==========================================
        mb.Entity<Cart>().Property(c => c.Id).HasColumnName("id");
        mb.Entity<Cart>().Property(c => c.UserEmail).HasColumnName("user_email");
        mb.Entity<Cart>().Property(c => c.ProductId).HasColumnName("product_id");   // string in model — varchar in DB ✓
        mb.Entity<Cart>().Property(c => c.ProductName).HasColumnName("product_name");
        mb.Entity<Cart>().Property(c => c.Quantity).HasColumnName("quantity");
        mb.Entity<Cart>().Property(c => c.Price).HasColumnName("price").HasColumnType("double");

        // ==========================================
        // CART ITEMS
        // ==========================================
        mb.Entity<CartItem>().Property(c => c.CartItemsId).HasColumnName("cart_items_id");
        mb.Entity<CartItem>().Property(c => c.CartId).HasColumnName("cart_id");
        mb.Entity<CartItem>().Property(c => c.ProductId).HasColumnName("product_id");
        mb.Entity<CartItem>().Property(c => c.Quantity).HasColumnName("quantity");
        mb.Entity<CartItem>().Property(c => c.Price).HasColumnName("price").HasColumnType("float");
        mb.Entity<CartItem>().Property(c => c.Subtotal).HasColumnName("subtotal").HasColumnType("float");

        // ==========================================
        // ORDERS
        // ==========================================
        mb.Entity<Order>().Property(o => o.OrderId).HasColumnName("order_id");
        mb.Entity<Order>().Property(o => o.CustomerId).HasColumnName("customer_id");
        mb.Entity<Order>().Property(o => o.OrderDate).HasColumnName("order_date");
        mb.Entity<Order>().Property(o => o.TotalAmount).HasColumnName("total_amount").HasPrecision(10, 2);
        mb.Entity<Order>().Property(o => o.Status).HasColumnName("status");
        mb.Entity<Order>().Property(o => o.PaymentMethod).HasColumnName("payment_method");
        mb.Entity<Order>().Property(o => o.PaymentStatus).HasColumnName("payment_status");
        mb.Entity<Order>().Property(o => o.TransactionId).HasColumnName("transaction_id");
        mb.Entity<Order>().Property(o => o.AddressId).HasColumnName("shipping_address");

        // ==========================================
        // ORDER ITEMS
        // ==========================================
        mb.Entity<OrderItem>().Property(oi => oi.OrderItemId).HasColumnName("order_item_id");
        mb.Entity<OrderItem>().Property(oi => oi.OrderId).HasColumnName("order_id");
        mb.Entity<OrderItem>().Property(oi => oi.ProductId).HasColumnName("product_id");
        mb.Entity<OrderItem>().Property(oi => oi.Quantity).HasColumnName("quantity");
        mb.Entity<OrderItem>().Property(oi => oi.PriceAtTime).HasColumnName("price_at_time").HasPrecision(10, 2);

        // ==========================================
        // PAYMENT LOGS
        // ==========================================
        mb.Entity<PaymentLog>().Property(p => p.LogId).HasColumnName("log_id");
        mb.Entity<PaymentLog>().Property(p => p.OrderId).HasColumnName("order_id");
        mb.Entity<PaymentLog>().Property(p => p.ResponseData).HasColumnName("response_data").HasColumnType("text");
        mb.Entity<PaymentLog>().Property(p => p.CreatedAt).HasColumnName("created_at");

        // ==========================================
        // PRESCRIPTION
        // ==========================================
        mb.Entity<Prescription>().Property(p => p.Id).HasColumnName("id");
        mb.Entity<Prescription>().Property(p => p.UserEmail).HasColumnName("user_email");
        mb.Entity<Prescription>().Property(p => p.DoctorName).HasColumnName("doctor_name");
        mb.Entity<Prescription>().Property(p => p.DatePrescribed).HasColumnName("date_prescribed").HasColumnType("date");
        mb.Entity<Prescription>().Property(p => p.Type).HasColumnName("type");
        mb.Entity<Prescription>().Property(p => p.Notes).HasColumnName("notes").HasColumnType("text");
        mb.Entity<Prescription>().Property(p => p.FileData).HasColumnName("file_data").HasColumnType("longblob");
        mb.Entity<Prescription>().Property(p => p.Response).HasColumnName("response");

        // ==========================================
        // CONSULTATION
        // ==========================================
        mb.Entity<Consultation>().Property(c => c.ConsultationId).HasColumnName("consultation_id");
        mb.Entity<Consultation>().Property(c => c.Fullname).HasColumnName("fullname");
        mb.Entity<Consultation>().Property(c => c.PhoneNumber).HasColumnName("phone_number");
        mb.Entity<Consultation>().Property(c => c.PreferredDate).HasColumnName("preferred_date").HasColumnType("date");
        mb.Entity<Consultation>().Property(c => c.ConsultationType).HasColumnName("consultation_type");
        mb.Entity<Consultation>().Property(c => c.Description).HasColumnName("Description"); // capital D per DDL
        mb.Entity<Consultation>().Property(c => c.Response).HasColumnName("response");
        mb.Entity<Consultation>().Property(c => c.Email).HasColumnName("email");

        // ==========================================
        // UNIQUE INDEXES  (match CREATE TABLE)
        // ==========================================
        mb.Entity<User>().HasIndex(u => u.Email).IsUnique();
        mb.Entity<User>().HasIndex(u => u.PhoneNumber).IsUnique();
        mb.Entity<Cart>().HasIndex(c => new { c.UserEmail, c.ProductId }).IsUnique();

        // ==========================================
        // RELATIONSHIPS  (only those with real DB FKs,
        // plus the soft FK address→users that your model relies on)
        // ==========================================

        // products.category_id → product_categories.category_id
        mb.Entity<Product>()
            .HasOne(p => p.Category).WithMany(c => c.Products)
            .HasForeignKey(p => p.CategoryId)
            .OnDelete(DeleteBehavior.Restrict);

        // orders.customer_id → users.user_id
        mb.Entity<Order>()
            .HasOne(o => o.Customer).WithMany(u => u.Orders)
            .HasForeignKey(o => o.CustomerId)
            .OnDelete(DeleteBehavior.Restrict);

        // orders.shipping_address → address.address_id
        mb.Entity<Order>()
            .HasOne(o => o.Address).WithMany()
            .HasForeignKey(o => o.AddressId)
            .OnDelete(DeleteBehavior.Restrict);

        // order_items.order_id → orders.order_id
        mb.Entity<OrderItem>()
            .HasOne(oi => oi.Order).WithMany(o => o.OrderItems)
            .HasForeignKey(oi => oi.OrderId)
            .OnDelete(DeleteBehavior.Restrict);

        // order_items.product_id → products.product_id
        mb.Entity<OrderItem>()
            .HasOne(oi => oi.Product).WithMany(p => p.OrderItems)
            .HasForeignKey(oi => oi.ProductId)
            .OnDelete(DeleteBehavior.Restrict);

        // payment_logs.order_id → orders.order_id
        mb.Entity<PaymentLog>()
            .HasOne(p => p.Order).WithMany(o => o.PaymentLogs)
            .HasForeignKey(p => p.OrderId)
            .OnDelete(DeleteBehavior.Restrict);

        // Soft FK (no constraint in DB, but model uses User.Addresses):
        mb.Entity<Address>()
            .HasOne(a => a.User).WithMany(u => u.Addresses)
            .HasForeignKey(a => a.UserId)
            .OnDelete(DeleteBehavior.Restrict);

        // Soft FK for legacy design (no constraint in DB, but original code used it):
        mb.Entity<CartItem>()
            .HasOne<Cart>().WithMany()
            .HasForeignKey(ci => ci.CartId)
            .OnDelete(DeleteBehavior.Restrict);

        // NOTE: cart_items.product_id has NO FK in the database.
        // Do NOT configure a relationship here — EF would enforce it at
        // SaveChanges and could reject legacy/orphan rows.

        // ==========================================
        // IGNORED (already [NotMapped] but explicit for clarity)
        // ==========================================
        mb.Entity<Order>().Ignore(o => o.Subtotal);
        mb.Entity<Order>().Ignore(o => o.ServiceFee);
        mb.Entity<Order>().Ignore(o => o.DeliveryAddressSnapshot);
    }
}