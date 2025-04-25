import { Pelanggan } from "./pelanggan";

export interface Komplain {
    id: number;
    customer_id: number;
    complaint: string;
    status: "Pending" | "In Progress" | "Resolved"; // Status keluhan
    created_at: string;
    updated_at: string;
    customer?: Pelanggan; // Relasi opsional dengan Customer
  }
  