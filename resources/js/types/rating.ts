import { Pelanggan } from "./pelanggan";

export interface Rating {
    id: number;
    customer_id: number;
    rating: number; // Skala 1 - 5
    feedback: string;
    created_at: string;
    updated_at: string;
    customer?: Pelanggan; // Relasi opsional dengan Customer
  }
  